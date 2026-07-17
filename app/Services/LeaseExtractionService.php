<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reads a lease contract (image or single-page PDF) via Google Gemini and returns
 * structured fields. Mirrors InvoiceExtractionService: the Gemini call is forced
 * into JSON via responseSchema; normalize()/validate() are pure (no HTTP) and
 * unit-tested. Prompt lives at resources/prompts/lease-extraction.md.
 */
class LeaseExtractionService
{
    /** The 22 lease fields (Spec 003 FR-201), in display order. */
    public const FIELDS = [
        'contract_no', 'tenant_name', 'tenant_id_no', 'landlord_name', 'landlord_id_no',
        'property_no', 'unit', 'property_type', 'address',
        'start_date', 'end_date', 'duration',
        'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
        'deposit', 'payment_method',
        'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
    ];

    /** Fields that must be present for a contract to be trusted without review. */
    private const REQUIRED_FIELDS = [
        'contract_no', 'tenant_name', 'landlord_name',
        'start_date', 'end_date', 'rent_value',
    ];

    /** usageMetadata from the most recent Gemini call (token counts). */
    public array $lastUsage = [];

    public function lastInputTokens(): int
    {
        return (int) ($this->lastUsage['promptTokenCount'] ?? 0);
    }

    public function lastOutputTokens(): int
    {
        return (int) ($this->lastUsage['candidatesTokenCount'] ?? 0) + (int) ($this->lastUsage['thoughtsTokenCount'] ?? 0);
    }

    /** USD cost for a token count, using the configured per-1M rates. */
    public function costUsd(int $inputTokens, int $outputTokens): float
    {
        return $inputTokens / 1_000_000 * (float) config('services.gemini.price_in_per_m', 1.5)
            + $outputTokens / 1_000_000 * (float) config('services.gemini.price_out_per_m', 9.0);
    }

    /**
     * Extract one lease contract from a single file (single-page PDF or image).
     * Returns the normalized fields plus 'raw_json' and validation result.
     */
    public function extractLease(string $filePath, ?string $model = null, ?string $thinking = null): array
    {
        $mime = $this->mimeFor($filePath);
        $raw = $this->callGemini($this->prompt(), $filePath, $mime, $this->schema(), $model, $thinking);

        $data = is_array($raw) ? $raw : [];
        $norm = $this->normalize($data);
        $validation = $this->validate($norm);

        return $norm + [
            'field_confidence' => $this->normalizeFieldConfidence($data['field_confidence'] ?? null),
            'raw_json' => $data,
            'needs_review' => $validation['needs_review'],
            'validation_notes' => $validation['notes'],
            '_in' => $this->lastInputTokens(),
            '_out' => $this->lastOutputTokens(),
        ];
    }

    // ----------------------------------------------------------------- pure logic

    /** Coerce model output into typed, storable values. */
    public function normalize(array $d): array
    {
        return [
            'contract_no' => $this->cleanStr($d['contract_no'] ?? null),
            'tenant_name' => $this->cleanStr($d['tenant_name'] ?? null),
            'tenant_id_no' => $this->digitsOnly($d['tenant_id_no'] ?? null),
            'landlord_name' => $this->cleanStr($d['landlord_name'] ?? null),
            'landlord_id_no' => $this->digitsOnly($d['landlord_id_no'] ?? null),
            'property_no' => $this->cleanStr($d['property_no'] ?? null),
            'unit' => $this->cleanStr($d['unit'] ?? null),
            'property_type' => $this->cleanStr($d['property_type'] ?? null),
            'address' => $this->cleanStr($d['address'] ?? null),
            'start_date' => $this->parseDate($d['start_date'] ?? null),
            'end_date' => $this->parseDate($d['end_date'] ?? null),
            'duration' => $this->cleanStr($d['duration'] ?? null),
            'rent_value' => $this->num($d['rent_value'] ?? null),
            'num_payments' => $this->int($d['num_payments'] ?? null),
            'payment_value' => $this->num($d['payment_value'] ?? null),
            'payment_frequency' => $this->cleanStr($d['payment_frequency'] ?? null),
            'deposit' => $this->num($d['deposit'] ?? null),
            'payment_method' => $this->cleanStr($d['payment_method'] ?? null),
            'renewal_terms' => $this->cleanStr($d['renewal_terms'] ?? null),
            'cancellation_terms' => $this->cleanStr($d['cancellation_terms'] ?? null),
            'increase_terms' => $this->cleanStr($d['increase_terms'] ?? null),
            'extra_terms' => $this->cleanStr($d['extra_terms'] ?? null),
            'confidence' => $this->num($d['confidence'] ?? null),
        ];
    }

    /** Keep only 0..1 numeric per-field confidences (Spec 001 FR-002). */
    public function normalizeFieldConfidence($fc): array
    {
        if (! is_array($fc)) {
            return [];
        }
        $out = [];
        foreach ($fc as $k => $v) {
            if (is_numeric($v)) {
                $out[$k] = max(0.0, min(1.0, (float) $v));
            }
        }

        return $out;
    }

    /**
     * Validate an extracted lease. Returns ['needs_review' => bool, 'notes' => string[]].
     * Rules: required fields present, dates sane (end after start), rent_value positive.
     */
    public function validate(array $d): array
    {
        $notes = [];

        foreach (self::REQUIRED_FIELDS as $f) {
            if (! isset($d[$f]) || $d[$f] === '' || $d[$f] === null) {
                $notes[] = "حقل مفقود: {$f}";
            }
        }

        $start = $d['start_date'] ?? null;
        $end = $d['end_date'] ?? null;
        if ($start !== null && $end !== null) {
            try {
                if (Carbon::parse($end)->lt(Carbon::parse($start))) {
                    $notes[] = 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية';
                }
            } catch (\Throwable $e) {
                $notes[] = 'تعذّر التحقق من التواريخ';
            }
        }

        $rent = $d['rent_value'] ?? null;
        if ($rent !== null && is_numeric($rent) && (float) $rent <= 0) {
            $notes[] = 'قيمة الإيجار يجب أن تكون أكبر من صفر';
        }

        return ['needs_review' => count($notes) > 0, 'notes' => $notes];
    }

    private function num($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = str_replace(['٬', ',', ' ', 'ر.س', 'SAR', 'SR'], '', (string) $v);
        $v = $this->arabicDigits($v);

        return is_numeric($v) ? (float) $v : null;
    }

    private function int($v): ?int
    {
        $n = $this->num($v);

        return $n === null ? null : (int) $n;
    }

    private function digitsOnly($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $this->arabicDigits((string) $v));

        return $digits === '' ? null : $digits;
    }

    private function cleanStr($v): ?string
    {
        if ($v === null) {
            return null;
        }
        $v = trim((string) $v);

        return $v === '' ? null : $v;
    }

    private function parseDate($v): ?string
    {
        $v = $this->cleanStr($v);
        if ($v === null) {
            return null;
        }
        try {
            return Carbon::parse($this->arabicDigits($v))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Convert Arabic-Indic digits to ASCII so numbers/dates parse. */
    private function arabicDigits(string $s): string
    {
        return strtr($s, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }

    // ----------------------------------------------------------------- Gemini call

    /** The hardened instruction set, editable at resources/prompts/lease-extraction.md. */
    private function prompt(): string
    {
        $file = resource_path('prompts/lease-extraction.md');
        if (is_file($file)) {
            return file_get_contents($file);
        }

        // Fallback so the service never breaks if the file is missing.
        return 'استخرج من عقد الإيجار: '.implode('، ', self::FIELDS)
            .'. أعد كل المفاتيح دائمًا، واستخدم null لأي حقل غير موجود. أعد JSON فقط.';
    }

    private function schema(): array
    {
        $numeric = ['rent_value', 'payment_value', 'deposit'];
        $properties = [];
        foreach (self::FIELDS as $f) {
            if ($f === 'num_payments') {
                $properties[$f] = ['type' => 'INTEGER', 'nullable' => true];
            } elseif (in_array($f, $numeric, true)) {
                $properties[$f] = ['type' => 'NUMBER', 'nullable' => true];
            } else {
                $properties[$f] = ['type' => 'STRING', 'nullable' => true];
            }
        }
        $properties['confidence'] = ['type' => 'NUMBER', 'nullable' => true];
        $properties['field_confidence'] = [
            'type' => 'OBJECT',
            'nullable' => true,
            'properties' => array_fill_keys(self::FIELDS, ['type' => 'NUMBER', 'nullable' => true]),
        ];

        return [
            'type' => 'OBJECT',
            'properties' => $properties,
            'required' => self::FIELDS,
            'propertyOrdering' => array_merge(self::FIELDS, ['confidence']),
        ];
    }

    /**
     * POST one document part + prompt to Gemini generateContent, return decoded JSON.
     *
     * @throws RuntimeException on HTTP failure (caller / job decides retry).
     */
    protected function callGemini(string $prompt, string $filePath, string $mime, array $schema, ?string $model, ?string $thinking = null): array
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        $model = $model ?: config('services.gemini.default_model');
        $base = rtrim(config('services.gemini.base_url'), '/');

        if (! is_file($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }

        $generationConfig = [
            'temperature' => 0,
            'responseMimeType' => 'application/json',
            'responseSchema' => $schema,
        ];
        $level = $thinking ?: config('services.gemini.thinking_level');
        if ($level && str_contains($model, 'gemini-3')) {
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => $level];
        }

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => [
                        'mime_type' => $mime,
                        'data' => base64_encode(file_get_contents($filePath)),
                    ]],
                ],
            ]],
            'generationConfig' => $generationConfig,
        ];

        // Retry transient errors (429 rate-limit, 5xx overload) with exponential backoff.
        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        $resp = null;
        while (true) {
            $attempt++;
            $resp = Http::timeout((int) config('services.gemini.timeout', 120))
                ->acceptJson()
                ->post($url, $body);

            if ($resp->successful()) {
                break;
            }

            $status = $resp->status();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                usleep((int) ((2 ** $attempt) * 500_000));
                continue;
            }

            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $this->lastUsage = (array) data_get($resp->json(), 'usageMetadata', []);

        return $this->decodeJsonResponse($resp->json());
    }

    /**
     * Robustly pull the JSON payload out of a Gemini generateContent response.
     * Thinking models return multiple parts (thought parts first) and can also
     * truncate into degenerate output (e.g. an endless "2222…" loop after a
     * valid JSON prefix when MAX_TOKENS hits). Strategy: scan all parts, skip
     * thought parts, strip markdown fences, then fall back to a balanced-brace
     * salvage of the first complete {...} object before giving up.
     */
    protected function decodeJsonResponse($json): array
    {
        $parts = (array) data_get($json, 'candidates.0.content.parts', []);
        $texts = [];
        foreach ($parts as $part) {
            if (! empty($part['thought'])) {
                continue; // thinking-model reasoning part — never the payload
            }
            if (isset($part['text']) && is_string($part['text'])) {
                $texts[] = $part['text'];
            }
        }
        if ($texts === []) {
            $fallback = data_get($json, 'candidates.0.content.parts.0.text');
            if (is_string($fallback)) {
                $texts[] = $fallback;
            }
        }
        if ($texts === []) {
            throw new RuntimeException('Gemini returned no content: '.json_encode($json));
        }

        foreach ($texts as $text) {
            $decoded = $this->tryDecodeJson($text);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        throw new RuntimeException('Gemini returned non-JSON: '.substr($texts[0], 0, 2000));
    }

    /** Decode one text part: direct, fence-stripped, then balanced-brace salvage. */
    private function tryDecodeJson(string $text): ?array
    {
        $text = trim($text);
        // strip ```json ... ``` fences if present
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $text, $m)) {
            $text = trim($m[1]);
        }
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // Salvage a complete top-level object prefix (handles truncated /
        // garbage-suffixed output like "{...valid...}2222222…").
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }
        $depth = 0;
        $inStr = false;
        $esc = false;
        $len = strlen($text);
        $lastCommaAtDepth1 = null;
        for ($i = $start; $i < $len; $i++) {
            $c = $text[$i];
            if ($inStr) {
                if ($esc) { $esc = false; }
                elseif ($c === '\\') { $esc = true; }
                elseif ($c === '"') { $inStr = false; }
                continue;
            }
            if ($c === '"') { $inStr = true; }
            elseif ($c === '{') { $depth++; }
            elseif ($c === ',') {
                if ($depth === 1) { $lastCommaAtDepth1 = $i; }
            }
            elseif ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    $candidate = substr($text, $start, $i - $start + 1);
                    $decoded = json_decode($candidate, true);
                    return is_array($decoded) ? $decoded : null;
                }
            }
        }

        // Truncated mid-string / mid-object (MAX_TOKENS): keep every complete
        // top-level pair, drop the unfinished tail, close the object.
        if ($lastCommaAtDepth1 !== null) {
            $candidate = substr($text, $start, $lastCommaAtDepth1 - $start).'}';
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function mimeFor(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
