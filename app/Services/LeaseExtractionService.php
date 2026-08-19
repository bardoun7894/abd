<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Reads a lease contract (image or single-page PDF) via Google Gemini and returns
 * structured fields. Mirrors InvoiceExtractionService: the Gemini call is forced
 * into JSON via responseSchema; normalize()/validate() are pure (no HTTP) and
 * unit-tested. Prompt lives at resources/prompts/lease-extraction.md.
 */
class LeaseExtractionService
{
    /**
     * The scalar lease fields (Spec 003 FR-201), in display order.
     *
     * `annual_rent` and `vat_amount` were added after a real extraction bug: an
     * official Saudi «إيجار» contract prints the pre-VAT annual rent, the VAT, and
     * the VAT-inclusive grand total side by side, and a single `rent_value` field
     * gave the model no way to tell them apart — so it sometimes returned the
     * pre-VAT figure and the whole schedule came out short by the VAT.
     * `rent_value` is now defined as the VAT-INCLUSIVE total; the other two are
     * captured explicitly so the split is visible and auditable.
     */
    public const FIELDS = [
        'contract_no', 'tenant_name', 'tenant_id_no', 'landlord_name', 'landlord_id_no',
        'property_no', 'unit', 'property_type', 'address',
        'start_date', 'end_date', 'duration',
        'rent_value', 'annual_rent', 'vat_amount',
        'num_payments', 'payment_value', 'payment_frequency',
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
        $gemini = new GeminiClient();
        $raw = $gemini->extract($this->prompt(), $filePath, $this->schema(), $model, $thinking);
        $this->lastUsage = $gemini->lastUsage;

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
            'annual_rent' => $this->num($d['annual_rent'] ?? null),
            'vat_amount' => $this->num($d['vat_amount'] ?? null),
            'num_payments' => $this->int($d['num_payments'] ?? null),
            'payment_value' => $this->num($d['payment_value'] ?? null),
            'payment_frequency' => $this->cleanStr($d['payment_frequency'] ?? null),
            'deposit' => $this->num($d['deposit'] ?? null),
            'payment_method' => $this->cleanStr($d['payment_method'] ?? null),
            'renewal_terms' => $this->cleanStr($d['renewal_terms'] ?? null),
            'cancellation_terms' => $this->cleanStr($d['cancellation_terms'] ?? null),
            'increase_terms' => $this->cleanStr($d['increase_terms'] ?? null),
            'extra_terms' => $this->cleanStr($d['extra_terms'] ?? null),
            'payments' => $this->normalizePayments($d['payments'] ?? null),
            'confidence' => $this->num($d['confidence'] ?? null),
        ];
    }

    /**
     * Normalize the payment schedule the contract itself prints (جدول سداد الدفعات).
     *
     * Saudi «إيجار» contracts print every installment with its own due date, rent,
     * VAT and total. Before this existed the app ignored that table and recomputed
     * the schedule from rent_value / num_payments at fixed monthly intervals, which
     * got both the amounts (VAT dropped) and the due dates wrong — real due dates
     * sit days AFTER the tenancy start, not exactly on it.
     *
     * Rows without a usable due date or total are dropped rather than guessed: a
     * half-read row is worse than falling back to the generator. Rows are returned
     * ordered by payment_no so a shuffled table still yields a sane schedule.
     *
     * @return array<int,array{payment_no:int,due_date:string,rent_value:?float,vat:?float,total:float}>
     */
    public function normalizePayments($rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach (array_values($rows) as $i => $row) {
            if (! is_array($row)) {
                continue;
            }

            $due = $this->parseDate($row['due_date'] ?? null);
            $total = $this->num($row['total'] ?? null);
            $rent = $this->num($row['rent_value'] ?? null);
            $vat = $this->num($row['vat'] ?? null);

            // A row is only usable if we know WHEN and HOW MUCH. Fall back to
            // rent+VAT when the total column itself was unreadable.
            if ($total === null && $rent !== null) {
                $total = round($rent + ($vat ?? 0), 2);
            }
            if ($due === null || $total === null || $total <= 0) {
                continue;
            }

            $no = $this->int($row['payment_no'] ?? null);
            $out[] = [
                'payment_no' => $no !== null && $no > 0 ? $no : $i + 1,
                'due_date' => $due,
                'rent_value' => $rent,
                'vat' => $vat,
                'total' => $total,
            ];
        }

        usort($out, fn ($a, $b) => $a['payment_no'] <=> $b['payment_no']);

        return $out;
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
        $numeric = ['rent_value', 'annual_rent', 'vat_amount', 'payment_value', 'deposit'];
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
        // The contract's own printed schedule (جدول سداد الدفعات). Declared in the
        // responseSchema so Gemini returns real objects rather than a prose blob.
        $properties['payments'] = [
            'type' => 'ARRAY',
            'nullable' => true,
            'items' => [
                'type' => 'OBJECT',
                'properties' => [
                    'payment_no' => ['type' => 'INTEGER', 'nullable' => true],
                    'due_date' => ['type' => 'STRING', 'nullable' => true],
                    'rent_value' => ['type' => 'NUMBER', 'nullable' => true],
                    'vat' => ['type' => 'NUMBER', 'nullable' => true],
                    'total' => ['type' => 'NUMBER', 'nullable' => true],
                ],
            ],
        ];
        $properties['confidence'] = ['type' => 'NUMBER', 'nullable' => true];
        $properties['field_confidence'] = [
            'type' => 'OBJECT',
            'nullable' => true,
            'properties' => array_fill_keys(self::FIELDS, ['type' => 'NUMBER', 'nullable' => true]),
        ];

        return [
            'type' => 'OBJECT',
            'properties' => $properties,
            // `payments` is deliberately NOT required: many contracts print no
            // schedule table at all, and forcing the key would invite invention.
            'required' => self::FIELDS,
            'propertyOrdering' => array_merge(self::FIELDS, ['payments', 'confidence']),
        ];
    }

}
