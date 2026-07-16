<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Spec 005 T-B1 — AI for the VIOLATION (مخالفات) module. Two text-only capabilities:
 *
 *  (a) classify(): given a free-text violation note, ask Gemini for a "side" hint, a
 *      severity hint, and a suggested action. `side` is matched to the real
 *      `violation_side` taxonomy rows (the only real constant table for this module —
 *      see resources/views/dashboard/constant/violation) via SupplierMatcher::nameSimilarity,
 *      exactly like MoraslatAiExtractor::suggestFromList. There is no real severity
 *      constant table anywhere in this app, so `severity` is matched against an optional
 *      caller-supplied list (if one is ever added) and otherwise falls back to a fixed set
 *      of Arabic severity labels, or the model's raw free-text hint if nothing matches well.
 *
 *  (b) draftNotice(): drafts a formal Arabic violation-notice letter from violation fields
 *      (name, violation type, date, note).
 *
 *  Both calls are TEXT-ONLY (no file), so — like MoraslatAiExtractor::draftReply() — they
 *  cannot reuse GeminiClient::extract() (file-input only). GeminiClient is shared and
 *  frozen (do not modify), so this class makes its own minimal text-only Gemini HTTP call,
 *  copied verbatim from MoraslatAiExtractor::callGeminiText().
 */
class ViolationAiExtractor
{
    /** Fallback Arabic severity labels used when no real severity taxonomy is supplied. */
    private const DEFAULT_SEVERITIES = ['بسيطة', 'متوسطة', 'جسيمة'];

    /** usageMetadata (token counts) from the most recent text call. */
    public array $lastDraftUsage = [];

    public function __construct(private GeminiClient $gemini) {}

    /**
     * @param  iterable  $sides  rows with violation_side_id / violation_side_name
     * @param  iterable  $severities  optional real severity rows (id/name shaped like
     *                    $sides) if a constant table for severity is ever added; unused
     *                    today — falls back to DEFAULT_SEVERITIES / free-text hint.
     * @return array{side:?string, side_id:?int, side_score:float, severity:?string,
     *               suggested_action:?string}
     */
    public function classify(string $note, iterable $sides = [], iterable $severities = []): array
    {
        $raw = $this->callGeminiText($this->classifyPrompt($note), null);
        $decoded = json_decode($this->stripCodeFence($raw), true);
        if (! is_array($decoded)) {
            $decoded = [];
        }

        [$sideId, $sideName, $sideScore] = $this->suggestFromList($decoded['side_hint'] ?? null, $sides, 'violation_side_id', 'violation_side_name');

        return [
            'side' => $sideName ?? $this->cleanStr($decoded['side_hint'] ?? null),
            'side_id' => $sideId,
            'side_score' => $sideScore,
            'severity' => $this->matchSeverity($decoded['severity_hint'] ?? null, $severities),
            'suggested_action' => $this->cleanStr($decoded['suggested_action'] ?? null),
        ];
    }

    /**
     * Draft a formal Arabic violation-notice letter from violation fields. Text-only —
     * no file involved.
     *
     * @param  array{name?:?string, violation_type?:?string, date?:?string, note?:?string}  $fields
     * @return array{draft:string}
     */
    public function draftNotice(array $fields, ?string $model = null): array
    {
        $text = $this->callGeminiText($this->draftPrompt($fields), $model);

        return [
            'draft' => trim($text),
        ];
    }

    /**
     * Match a free-text hint (from the model) to the closest real taxonomy row by name
     * similarity. Pure w.r.t. its inputs — reuses SupplierMatcher::nameSimilarity.
     *
     * @return array{0:?int,1:?string,2:float} [id, name, score]
     */
    public function suggestFromList(?string $hint, iterable $rows, string $idKey, string $nameKey): array
    {
        $hint = trim((string) $hint);
        if ($hint === '') {
            return [null, null, 0.0];
        }

        $best = null;
        $bestScore = 0.0;
        foreach ($rows as $row) {
            $name = is_array($row) ? ($row[$nameKey] ?? '') : ($row->{$nameKey} ?? '');
            $score = SupplierMatcher::nameSimilarity($hint, (string) $name);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        if ($best && $bestScore >= 0.55) {
            $id = is_array($best) ? $best[$idKey] : $best->{$idKey};
            $name = is_array($best) ? $best[$nameKey] : $best->{$nameKey};

            return [(int) $id, $name, $bestScore];
        }

        return [null, null, 0.0];
    }

    /**
     * Match a severity hint against a caller-supplied taxonomy (rows may be plain strings
     * or objects/arrays exposing a `name`/`violation_severity_name` key). Falls back to a
     * fixed set of Arabic severity labels when no taxonomy is supplied, and finally to the
     * model's raw free-text hint when nothing matches confidently. Pure w.r.t. its inputs.
     */
    public function matchSeverity(?string $hint, iterable $severities = []): ?string
    {
        $hint = $this->cleanStr($hint);
        if ($hint === null) {
            return null;
        }

        $labels = [];
        foreach ($severities as $row) {
            if (is_string($row)) {
                $labels[] = $row;
            } elseif (is_array($row)) {
                $labels[] = $row['name'] ?? ($row['violation_severity_name'] ?? null);
            } else {
                $labels[] = $row->name ?? ($row->violation_severity_name ?? null);
            }
        }
        $labels = array_values(array_filter($labels, fn ($v) => $v !== null && $v !== ''));
        if (empty($labels)) {
            $labels = self::DEFAULT_SEVERITIES;
        }

        $best = null;
        $bestScore = 0.0;
        foreach ($labels as $label) {
            $score = SupplierMatcher::nameSimilarity($hint, (string) $label);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $label;
            }
        }

        return $bestScore >= 0.55 ? $best : $hint;
    }

    private function classifyPrompt(string $note): string
    {
        return "أنت مساعد تصنيف مخالفات إدارية للمحلات. اقرأ وصف المخالفة التالي وأعد JSON فقط دون أي تنسيق Markdown أو تعليق إضافي، بالمفاتيح التالية:\n"
            .'- side_hint: أقرب جهة للمخالفة (نص قصير مثل: البلدية، الأمانة، الدفاع المدني، أو أي جهة يذكرها النص).'."\n"
            .'- severity_hint: درجة خطورة المخالفة، اختر من: [بسيطة، متوسطة، جسيمة] حسب شدة المخالفة الموصوفة.'."\n"
            .'- suggested_action: إجراء مقترح موجز للتعامل مع المخالفة (جملة واحدة بالعربية).'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن؛ استخدم null لأي حقل غير واضح من النص. أعد كائن JSON صِرف فقط بدون أي نص إضافي.'."\n\n"
            .'وصف المخالفة: '.$this->arabicDigits($note);
    }

    private function draftPrompt(array $fields): string
    {
        $name = $this->cleanStr($fields['name'] ?? null) ?? 'المعني بالمخالفة';
        $type = $this->cleanStr($fields['violation_type'] ?? null) ?? 'غير محددة';
        $date = $this->cleanStr($fields['date'] ?? null) ?? 'غير محدد';
        $note = $this->cleanStr($fields['note'] ?? null) ?? '';

        return "أنت مساعد صياغة إنذارات مخالفات إدارية رسمية بالعربية الفصحى. بناءً على البيانات التالية، اكتب مسودة خطاب إنذار رسمي ومهذب ومختصر بخصوص مخالفة، يبدأ بالتحية المناسبة الموجّهة إلى الطرف المخالف، ويوضح نوع المخالفة وتاريخها وسببها، وينتهي بعبارة ختامية رسمية تطلب تصويب الوضع خلال مدة مناسبة. أعد نص الخطاب فقط دون أي تعليق إضافي أو تنسيق JSON.\n\n"
            ."اسم الطرف المعني: {$name}\n"
            ."نوع المخالفة: {$type}\n"
            ."تاريخ المخالفة: {$date}\n"
            .'ملاحظات/سبب المخالفة: '.$this->arabicDigits($note);
    }

    /**
     * Minimal text-only Gemini call (no inline file), mirroring GeminiClient::extract()'s
     * retry/backoff/error handling. GeminiClient itself is file-input only, so it can't be
     * reused as-is for plain-text generation; this keeps the shared client untouched.
     *
     * Copied verbatim from MoraslatAiExtractor::callGeminiText() per the frozen-file rule.
     */
    private function callGeminiText(string $prompt, ?string $model = null): string
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        $model = $model ?: config('services.gemini.default_model');
        $base = rtrim(config('services.gemini.base_url'), '/');

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'text/plain',
            ],
        ];

        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        while (true) {
            $attempt++;
            $resp = Http::timeout((int) config('services.gemini.timeout', 120))->acceptJson()->post($url, $body);
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

        $this->lastDraftUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');
        if ($text === null) {
            throw new RuntimeException('Gemini returned no content: '.$resp->body());
        }

        return $text;
    }

    /** Strip ```json ... ``` / ``` ... ``` code fences some models still wrap JSON in. */
    private function stripCodeFence(string $s): string
    {
        $s = trim($s);
        if (str_starts_with($s, '```')) {
            $s = preg_replace('/^```[a-zA-Z]*\s*/', '', $s);
            $s = preg_replace('/```\s*$/', '', $s);
        }

        return trim($s);
    }

    // ---- small pure normalizers ----
    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    private function arabicDigits(string $s): string
    {
        return strtr($s, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    }
}
