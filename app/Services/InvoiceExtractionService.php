<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Reads a single scanned Arabic Saudi VAT invoice (image or single-page PDF),
 * or a whole multi-invoice PDF, via Google Gemini and returns structured fields.
 *
 * The Gemini call is forced into JSON via responseSchema, so we get typed data
 * instead of free text. validate()/normalize() are pure (no HTTP) and unit-tested.
 */
class InvoiceExtractionService
{
    /** The 7 fields the client asked for, in display order. */
    public const FIELDS = [
        'supplier_name', 'supplier_tax_number', 'invoice_number', 'invoice_date',
        'amount_before_vat', 'vat_amount', 'total_incl_vat',
    ];

    /** usageMetadata from the most recent Gemini call (token counts). */
    public array $lastUsage = [];

    /** Input tokens of the most recent call. */
    public function lastInputTokens(): int
    {
        return (int) ($this->lastUsage['promptTokenCount'] ?? 0);
    }

    /** Output tokens (response + billed thinking tokens) of the most recent call. */
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
     * Extract one invoice from a single file (single-page PDF or image).
     * Returns the normalized fields plus 'raw_json' and validation result.
     */
    public function extractInvoice(string $filePath, ?string $model = null, ?string $thinking = null): array
    {
        $mime = $this->mimeFor($filePath);
        $raw = $this->callGemini($this->prompt(false), $filePath, $mime, $this->singleSchema(), $model, $thinking);

        $data = is_array($raw) ? $raw : [];
        $norm = $this->normalize($data);
        $validation = $this->validate($norm);

        return $norm + [
            'line_items' => $this->normalizeLineItems($data['line_items'] ?? null),
            'field_confidence' => $this->normalizeFieldConfidence($data['field_confidence'] ?? null),
            'raw_json' => $data,
            'needs_review' => $validation['needs_review'],
            'validation_notes' => $validation['notes'],
            '_in' => $this->lastInputTokens(),
            '_out' => $this->lastOutputTokens(),
        ];
    }

    /**
     * Extract ALL invoices from a whole PDF in one call (fallback when the PDF
     * can't be split per-page). Returns ['invoices' => [...normalized...], 'raw_json' => ...].
     */
    public function extractInvoicesFromDocument(string $pdfPath, ?string $model = null, ?string $thinking = null): array
    {
        $raw = $this->callGemini($this->prompt(true), $pdfPath, 'application/pdf', $this->documentSchema(), $model, $thinking);

        $list = $raw['invoices'] ?? (array_is_list($raw ?? []) ? $raw : []);
        $invoices = [];
        $page = 1;
        foreach ($list as $row) {
            if (! is_array($row)) {
                continue;
            }
            $norm = $this->normalize($row);
            if (empty($norm['page_number'])) {
                $norm['page_number'] = $page;
            }
            $validation = $this->validate($norm);
            $invoices[] = $norm + [
                'line_items' => $this->normalizeLineItems($row['line_items'] ?? null),
                'field_confidence' => $this->normalizeFieldConfidence($row['field_confidence'] ?? null),
                'raw_json' => $row,
                'needs_review' => $validation['needs_review'],
                'validation_notes' => $validation['notes'],
            ];
            $page++;
        }

        return ['invoices' => $invoices, 'raw_json' => $raw, 'in' => $this->lastInputTokens(), 'out' => $this->lastOutputTokens()];
    }

    /**
     * PHASE 1 — count/segment only. Returns how many invoices are in the PDF and
     * the page range of each, WITHOUT extracting the detail fields. Cheap + fast.
     *
     * @return array{count:int, segments: array<int, array{invoice_number:?string,start_page:int,end_page:int}>}
     */
    public function countInvoices(string $pdfPath, ?string $model = null): array
    {
        $prompt = 'هذا ملف PDF قد يحتوي على فاتورة واحدة أو عدة فواتير ضريبية. '
            .'لا تستخرج تفاصيل البنود ولا المبالغ. مهمتك فقط: عدّ عدد الفواتير المستقلة وحدّد نطاق صفحات كل فاتورة. '
            .'كل فاتورة لها رقم فاتورة مستقل (invoice_number) وملخص خاص بها (Grand Total / المجموع). '
            .'قد تمتد الفاتورة الواحدة لعدة صفحات فاعتبرها فاتورة واحدة (نفس رقم الفاتورة). '
            .'أعد total_invoices وعنصرًا لكل فاتورة فيه invoice_number و start_page و end_page (أرقام الصفحات تبدأ من 1).';

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'total_invoices' => ['type' => 'INTEGER'],
                'invoices' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'invoice_number' => ['type' => 'STRING', 'nullable' => true],
                            'start_page' => ['type' => 'INTEGER'],
                            'end_page' => ['type' => 'INTEGER'],
                        ],
                    ],
                ],
            ],
        ];

        $raw = $this->callGemini($prompt, $pdfPath, 'application/pdf', $schema, $model);

        $segments = [];
        foreach (($raw['invoices'] ?? []) as $i => $seg) {
            if (! is_array($seg)) {
                continue;
            }
            $start = isset($seg['start_page']) ? max(1, (int) $seg['start_page']) : ($i + 1);
            $end = isset($seg['end_page']) ? max($start, (int) $seg['end_page']) : $start;
            $segments[] = [
                'invoice_number' => $this->cleanStr($seg['invoice_number'] ?? null),
                'start_page' => $start,
                'end_page' => $end,
            ];
        }

        $count = $raw['total_invoices'] ?? count($segments);

        return ['count' => (int) $count, 'segments' => $segments];
    }

    /**
     * FREE (no-AI) invoice count. Strategy:
     *  - Digital PDF (has a text layer): count invoice markers in the extracted text.
     *  - Image scan (no text): fall back to page count (1 invoice per page assumption).
     * Pure-PHP page count via FPDI works on shared hosting; pdftotext is used only if available.
     *
     * @return array{count:int, pages:int, has_text:bool, method:string}
     */
    public function countInvoicesFree(string $pdfPath): array
    {
        $pages = $this->pdfPageCount($pdfPath);
        $text = $this->pdfText($pdfPath);
        $hasText = mb_strlen(trim($text)) > 40;

        if ($hasText) {
            // Each invoice usually starts a "Page 1/N" block; otherwise count tax-invoice headers.
            $byPageMarker = preg_match_all('~page\s*1\s*/\s*\d~i', $text);
            $byHeader = preg_match_all('~tax\s*invoice~i', $text);
            $count = max($byPageMarker, $byHeader);
            if ($count < 1) {
                $count = max($pages, 1);

                return ['count' => $count, 'pages' => $pages, 'has_text' => true, 'method' => 'text present but no invoice marker — used page count'];
            }

            return ['count' => $count, 'pages' => $pages, 'has_text' => true, 'method' => 'text markers (free)'];
        }

        return ['count' => max($pages, 1), 'pages' => $pages, 'has_text' => false, 'method' => 'image scan — page count, assumes 1 invoice/page (free)'];
    }

    /** Pure-PHP page count (FPDI). Falls back to 1 on parse failure. */
    public function pdfPageCount(string $pdfPath): int
    {
        try {
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();

            return (int) $pdf->setSourceFile($pdfPath);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /** Extract embedded text via pdftotext when available; '' for image scans / no poppler. */
    private function pdfText(string $pdfPath): string
    {
        if (! function_exists('shell_exec')) {
            return '';
        }
        $bin = trim((string) @shell_exec('command -v pdftotext'));
        if ($bin === '') {
            return '';
        }
        $out = @shell_exec('pdftotext '.escapeshellarg($pdfPath).' - 2>/dev/null');

        return is_string($out) ? $out : '';
    }

    /**
     * Merge per-page extraction rows into one record per invoice number.
     * Handles multi-page invoices (same invoice number across pages). Rows with no
     * invoice number each stay their own invoice. Pure — unit-testable.
     *
     * @param  array<int, array>  $rows  each row = normalized fields + page_number
     * @return array<int, array>
     */
    public function groupByInvoiceNumber(array $rows): array
    {
        $groups = [];
        $merged = [];

        foreach ($rows as $r) {
            $key = $r['invoice_number'] ?? null;
            if ($key === null || $key === '') {
                $merged[] = $r; // can't group — keep as its own invoice

                continue;
            }
            $groups[$key][] = $r;
        }

        foreach ($groups as $g) {
            $merged[] = count($g) === 1 ? $g[0] : $this->mergeInvoiceRows($g);
        }

        // stable order by first page
        usort($merged, fn ($a, $b) => ($a['page_number'] ?? 0) <=> ($b['page_number'] ?? 0));

        return $merged;
    }

    /** Combine multiple pages of one invoice: prefer the page carrying the totals. */
    private function mergeInvoiceRows(array $rows): array
    {
        // Base = the page that has a total (the summary page); else the first page.
        $base = $rows[0];
        foreach ($rows as $r) {
            if (($r['total_incl_vat'] ?? null) !== null) {
                $base = $r;
                break;
            }
        }
        // Fill any still-missing field from the other pages.
        foreach ($rows as $r) {
            foreach ($r as $k => $v) {
                if (($base[$k] ?? null) === null && $v !== null) {
                    $base[$k] = $v;
                }
            }
        }
        $base['page_number'] = min(array_map(fn ($r) => $r['page_number'] ?? PHP_INT_MAX, $rows));

        return $base;
    }

    // ----------------------------------------------------------------- pure logic

    /** Coerce model output into typed, storable values. */
    public function normalize(array $d): array
    {
        return [
            'supplier_name' => $this->cleanStr($d['supplier_name'] ?? null),
            'supplier_tax_number' => $this->digitsOnly($d['supplier_tax_number'] ?? null),
            'invoice_number' => $this->cleanStr($d['invoice_number'] ?? null),
            'invoice_date' => $this->parseDate($d['invoice_date'] ?? null),
            'invoice_date_raw' => $this->cleanStr($d['invoice_date'] ?? null),
            'amount_before_vat' => $this->num($d['amount_before_vat'] ?? null),
            'vat_amount' => $this->num($d['vat_amount'] ?? null),
            'total_incl_vat' => $this->num($d['total_incl_vat'] ?? null),
            'confidence' => $this->num($d['confidence'] ?? null),
            'image_quality' => $this->quality($d['image_quality'] ?? null),
            'page_number' => isset($d['page_number']) && is_numeric($d['page_number']) ? (int) $d['page_number'] : null,

            // Spec 002 FR-101 — extended header fields.
            'invoice_type' => $this->invoiceType($d['invoice_type'] ?? null),
            'currency' => $this->cleanStr($d['currency'] ?? null),
            'discount_total' => $this->num($d['discount_total'] ?? null),
            'vat_rate' => $this->num($d['vat_rate'] ?? null),
            'commercial_registration' => $this->digitsOnly($d['commercial_registration'] ?? null),
            'payment_method' => $this->cleanStr($d['payment_method'] ?? null),
            'due_date' => $this->parseDate($d['due_date'] ?? null),
            'issuer_establishment_name' => $this->cleanStr($d['issuer_establishment_name'] ?? null),
            'notes' => $this->cleanStr($d['notes'] ?? null),
        ];
    }

    /** Coerce the model's invoice-type into one of: tax | simplified. Unknown -> null. */
    private function invoiceType($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = strtolower(trim((string) $v));
        $map = [
            'tax' => 'tax', 'ضريبية' => 'tax', 'فاتورة ضريبية' => 'tax',
            'simplified' => 'simplified', 'مبسطة' => 'simplified', 'ضريبية مبسطة' => 'simplified',
            'فاتورة ضريبية مبسطة' => 'simplified', 'simple' => 'simplified',
        ];

        return $map[$v] ?? null;
    }

    /**
     * Normalize the model's line_items array into typed rows (Spec 002 FR-102).
     * Pure + testable. Drops entirely-empty rows.
     *
     * @return array<int, array>
     */
    public function normalizeLineItems($items): array
    {
        if (! is_array($items)) {
            return [];
        }
        $out = [];
        foreach ($items as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $r = [
                'line_no' => $i + 1,
                'name' => $this->cleanStr($row['name'] ?? null),
                'quantity' => $this->num($row['quantity'] ?? null),
                'unit' => $this->cleanStr($row['unit'] ?? null),
                'unit_price' => $this->num($row['unit_price'] ?? null),
                'line_total' => $this->num($row['line_total'] ?? null),
                'vat_rate' => $this->num($row['vat_rate'] ?? null),
                'vat_amount' => $this->num($row['vat_amount'] ?? null),
            ];
            // Skip a row where every value field is null/empty.
            $vals = array_filter($r, fn ($v, $k) => $k !== 'line_no' && $v !== null && $v !== '', ARRAY_FILTER_USE_BOTH);
            if (! empty($vals)) {
                $out[] = $r;
            }
        }

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

    /** Normalize an invoice number for duplicate comparison (trim, strip spaces, upper). */
    public static function normNumber($n): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) $n)));
    }

    /**
     * The normalized invoice numbers that appear MORE THAN ONCE in $numbers.
     * Pure + testable — drives duplicate flagging (genuine repeats OR misread collisions).
     */
    public static function duplicateNumbers(array $numbers): array
    {
        $counts = [];
        foreach ($numbers as $n) {
            $k = self::normNumber($n);
            if ($k === '') {
                continue;
            }
            $counts[$k] = ($counts[$k] ?? 0) + 1;
        }

        return array_keys(array_filter($counts, fn ($c) => $c > 1));
    }

    /**
     * Coerce the model's image-quality rating into one of: clear | medium | unclear.
     * Accepts common synonyms; unknown/empty -> null.
     */
    private function quality($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = strtolower(trim((string) $v));

        $map = [
            'clear' => 'clear', 'good' => 'clear', 'high' => 'clear', 'sharp' => 'clear',
            'readable' => 'clear', 'واضحة' => 'clear', 'واضح' => 'clear', 'جيدة' => 'clear', 'جيد' => 'clear',
            'medium' => 'medium', 'moderate' => 'medium', 'average' => 'medium', 'ok' => 'medium',
            'fair' => 'medium', 'متوسطة' => 'medium', 'متوسط' => 'medium',
            'unclear' => 'unclear', 'bad' => 'unclear', 'poor' => 'unclear', 'low' => 'unclear',
            'blurry' => 'unclear', 'blurred' => 'unclear', 'illegible' => 'unclear', 'unreadable' => 'unclear',
            'غير واضحة' => 'unclear', 'غير واضح' => 'unclear', 'رديئة' => 'unclear', 'سيئة' => 'unclear',
        ];

        return $map[$v] ?? null;
    }

    /**
     * Validate an invoice. Returns ['needs_review' => bool, 'notes' => string[]].
     * Saudi rules: 15-digit tax number starting+ending with 3; VAT ≈ 15%; total reconciles.
     */
    public function validate(array $d): array
    {
        $notes = [];

        // Spec 002 FR-101 — simplified tax invoices (فاتورة ضريبية مبسطة) may legitimately
        // omit the supplier VAT number, so don't flag it missing for that type.
        $simplified = ($d['invoice_type'] ?? null) === 'simplified';
        foreach (self::FIELDS as $f) {
            if ($simplified && $f === 'supplier_tax_number') {
                continue;
            }
            if (! isset($d[$f]) || $d[$f] === '' || $d[$f] === null) {
                $notes[] = "حقل مفقود: {$f}";
            }
        }

        $tax = $d['supplier_tax_number'] ?? null;
        if ($tax !== null && $tax !== '') {
            if (! preg_match('/^\d{15}$/', (string) $tax)) {
                $notes[] = 'الرقم الضريبي يجب أن يكون 15 رقمًا';
            } elseif ((string) $tax[0] !== '3' || substr((string) $tax, -1) !== '3') {
                $notes[] = 'الرقم الضريبي السعودي يبدأ بـ 3 وينتهي بـ 3';
            }
        }

        $base = $d['amount_before_vat'] ?? null;
        $vat = $d['vat_amount'] ?? null;
        $total = $d['total_incl_vat'] ?? null;

        if (is_numeric($base) && is_numeric($vat)) {
            $tol = max(0.02 * max(1, abs((float) $base)), 0.10);
            if (abs((float) $vat - ((float) $base * 0.15)) > $tol) {
                $notes[] = 'قيمة الضريبة لا تساوي 15% من المبلغ';
            }
        }

        if (is_numeric($base) && is_numeric($vat) && is_numeric($total)) {
            if (abs(((float) $base + (float) $vat) - (float) $total) > 0.10) {
                $notes[] = 'الإجمالي لا يساوي المبلغ + الضريبة';
            }
        }

        // Spec 002 FR-105 — no negative values on any monetary field.
        foreach (['amount_before_vat' => 'المبلغ قبل الضريبة', 'vat_amount' => 'الضريبة',
                  'total_incl_vat' => 'الإجمالي', 'discount_total' => 'الخصم'] as $f => $label) {
            if (isset($d[$f]) && is_numeric($d[$f]) && (float) $d[$f] < 0) {
                $notes[] = "قيمة سالبة غير صحيحة في {$label}";
            }
        }

        // Spec 002 FR-105 — line items must reconcile with the invoice total (بنود ↔ الإجمالي).
        if (! empty($d['line_items']) && is_array($d['line_items'])) {
            $sum = 0.0;
            $haveAny = false;
            foreach ($d['line_items'] as $li) {
                if (isset($li['line_total']) && is_numeric($li['line_total'])) {
                    $sum += (float) $li['line_total'];
                    $haveAny = true;
                }
            }
            // Compare the line-item sum to the pre-VAT amount (line totals are usually pre-VAT).
            if ($haveAny && is_numeric($base)) {
                $tol = max(0.02 * max(1, abs((float) $base)), 0.10);
                if (abs($sum - (float) $base) > $tol) {
                    $notes[] = 'مجموع البنود لا يطابق إجمالي الفاتورة — تحقّق من البنود';
                }
            }
        }

        // Any scan that isn't perfectly clear gets flagged — the numbers (esp. the
        // invoice number) can't be trusted on degraded scans, so a human must verify.
        $q = $d['image_quality'] ?? null;
        if ($q === 'unclear') {
            $notes[] = 'الصورة غير واضحة — يصعب قراءة بيانات الفاتورة، تحقّق من الأرقام';
        } elseif ($q === 'medium') {
            $notes[] = 'جودة الصورة متوسطة — تحقّق من رقم الفاتورة والأرقام';
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

    /** The hardened instruction set, editable at resources/prompts/invoice-extraction.md. */
    private function corePrompt(): string
    {
        $file = resource_path('prompts/invoice-extraction.md');
        if (is_file($file)) {
            return file_get_contents($file);
        }

        // Fallback so the service never breaks if the file is missing.
        return 'استخرج من الفاتورة الضريبية السعودية: supplier_name، supplier_tax_number (15 رقمًا، رقم '
            .'البائع أعلى الفاتورة وليس العميل)، invoice_number، invoice_date (YYYY-MM-DD)، '
            .'amount_before_vat، vat_amount، total_incl_vat (من ملخص الفاتورة بالأسفل وليس البنود). '
            .'أعد كل المفاتيح دائمًا، واستخدم null لأي حقل غير موجود. أعد JSON فقط.';
    }

    private function prompt(bool $multi): string
    {
        $core = $this->corePrompt();

        // Quality self-assessment: the model rates how legible the source image is.
        // Strict: any ambiguity in the key numbers must drop the rating, and numbers
        // must be transcribed exactly (never padded/guessed) — wrong invoice numbers
        // on smudged CamScanner receipts were slipping through as "medium".
        $quality = "\n\n## تقييم جودة الصورة (مهم)\n"
            .'أعد الحقل image_quality بإحدى القيم: '
            .'"clear" (كل الأرقام والنصوص واضحة تمامًا وغير قابلة للّبس)، '
            .'"medium" (مقروءة لكن بعض الأجزاء باهتة أو فيها ظلال)، '
            .'"unclear" (مشوشة أو فيها بقع/ظلال تغطّي البيانات أو يصعب تمييز بعض الأرقام). '
            .'قاعدة صارمة: إذا كان أي رقم في **رقم الفاتورة** أو **الرقم الضريبي** أو **الإجماليات** '
            .'غامضًا أو مطموسًا أو مغطّى جزئيًا أو يحتمل أكثر من قراءة، فاجعل التقييم "unclear" ولا تستخدم "medium". '
            .'انسخ رقم الفاتورة والرقم الضريبي **حرفًا حرفًا كما يظهر تمامًا** — لا تُضِف ولا تحذف ولا تخمّن أي خانة؛ '
            .'وإن تعذّر تمييز خانة واحدة فاجعل التقييم "unclear".';

        if ($multi) {
            $core .= $quality;

            return $core."\n\n## وضع متعدد الفواتير\n"
                .'قد يحتوي هذا الملف على فاتورة واحدة أو عدة فواتير (قد تتجاوز 100). '
                .'لكل فاتورة مستقلة — وليس لكل صفحة ولا لكل بند — أعد سجلاً واحدًا بالحقول أعلاه '
                .'وأضف page_number (رقم الصفحة التي تبدأ فيها الفاتورة) و image_quality لكل فاتورة. '
                .'قد تمتد الفاتورة الواحدة لأكثر من صفحة فاعتبرها فاتورة واحدة. '
                .'أعد كائن JSON فيه مصفوفة باسم invoices، عنصر واحد لكل فاتورة، وكل عنصر يحتوي كل المفاتيح.';
        }

        return $core.$quality."\n\n## وضع فاتورة واحدة\nهذه صفحة فاتورة ضريبية واحدة. استخرج سجلًا واحدًا بكل الحقول المطلوبة.";
    }

    private function singleSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'supplier_name' => ['type' => 'STRING', 'nullable' => true],
                'supplier_tax_number' => ['type' => 'STRING', 'nullable' => true],
                'invoice_number' => ['type' => 'STRING', 'nullable' => true],
                'invoice_date' => ['type' => 'STRING', 'nullable' => true],
                'amount_before_vat' => ['type' => 'NUMBER', 'nullable' => true],
                'vat_amount' => ['type' => 'NUMBER', 'nullable' => true],
                'total_incl_vat' => ['type' => 'NUMBER', 'nullable' => true],
                'confidence' => ['type' => 'NUMBER', 'nullable' => true],
                'image_quality' => ['type' => 'STRING', 'enum' => ['clear', 'medium', 'unclear'], 'nullable' => true],

                // Spec 002 FR-101 — extended header fields (all nullable/additive).
                'invoice_type' => ['type' => 'STRING', 'enum' => ['tax', 'simplified'], 'nullable' => true],
                'currency' => ['type' => 'STRING', 'nullable' => true],
                'discount_total' => ['type' => 'NUMBER', 'nullable' => true],
                'vat_rate' => ['type' => 'NUMBER', 'nullable' => true],
                'commercial_registration' => ['type' => 'STRING', 'nullable' => true],
                'payment_method' => ['type' => 'STRING', 'nullable' => true],
                'due_date' => ['type' => 'STRING', 'nullable' => true],
                'issuer_establishment_name' => ['type' => 'STRING', 'nullable' => true],
                'notes' => ['type' => 'STRING', 'nullable' => true],

                // Spec 002 FR-102 — line items (بنود الفاتورة).
                'line_items' => [
                    'type' => 'ARRAY',
                    'nullable' => true,
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'name' => ['type' => 'STRING', 'nullable' => true],
                            'quantity' => ['type' => 'NUMBER', 'nullable' => true],
                            'unit' => ['type' => 'STRING', 'nullable' => true],
                            'unit_price' => ['type' => 'NUMBER', 'nullable' => true],
                            'line_total' => ['type' => 'NUMBER', 'nullable' => true],
                            'vat_rate' => ['type' => 'NUMBER', 'nullable' => true],
                            'vat_amount' => ['type' => 'NUMBER', 'nullable' => true],
                        ],
                    ],
                ],

                // Spec 001 FR-002 — per-field confidence (0..1) for the key fields.
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => array_fill_keys(self::FIELDS, ['type' => 'NUMBER', 'nullable' => true]),
                ],
            ],
            // Force the original key fields to always appear; new fields stay optional so
            // the model may null them and the legacy behavior is unchanged.
            'required' => array_merge(self::FIELDS, ['image_quality']),
            'propertyOrdering' => array_merge(self::FIELDS, ['confidence', 'image_quality']),
        ];
    }

    private function documentSchema(): array
    {
        $item = $this->singleSchema();
        $item['properties']['page_number'] = ['type' => 'INTEGER', 'nullable' => true];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'invoices' => ['type' => 'ARRAY', 'items' => $item],
            ],
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
        // Gemini 3.x reasoning effort. Default 'minimal' (cheapest) for clear scans;
        // the pipeline passes a higher level when re-reading a bad scan.
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

        // Retry transient errors (429 rate-limit, 5xx overload) with exponential backoff —
        // the cheap lite models 503 often under load.
        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        $resp = null;
        $lastStatus = null;
        $lastBody = null;
        while (true) {
            $attempt++;
            $resp = Http::timeout((int) config('services.gemini.page_timeout', 120))
                ->acceptJson()
                ->post($url, $body);

            if ($resp->successful()) {
                break;
            }

            $status = $resp->status();
            $lastStatus = $status;
            $lastBody = $resp->body();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                Log::warning('Gemini transient HTTP error; retrying', [
                    'model' => $model,
                    'status' => $status,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'operation' => 'invoice_extraction',
                ]);
                usleep((int) ((2 ** $attempt) * 500_000)); // 1s, 2s, 4s, 8s ...
                continue;
            }

            Log::error('Gemini HTTP request failed after retries', [
                'model' => $model,
                'status' => $status,
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'operation' => 'invoice_extraction',
                'response' => substr($lastBody, 0, 1000),
            ]);
            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $this->lastUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        Log::info('Gemini invoice extraction completed', [
            'model' => $model,
            'attempts' => $attempt,
            'operation' => 'invoice_extraction',
            'input_tokens' => $this->lastInputTokens(),
            'output_tokens' => $this->lastOutputTokens(),
        ]);

        return $this->decodeJsonResponse($resp->json());
    }

    /**
     * Robustly pull the JSON payload out of a Gemini generateContent response.
     * Thinking models return multiple parts (thought parts first) and can also
     * truncate into degenerate output. Strategy: scan all parts, skip thought
     * parts, strip markdown fences, then fall back to a balanced-brace salvage.
     */
    protected function decodeJsonResponse($json): array
    {
        $parts = (array) data_get($json, 'candidates.0.content.parts', []);
        $texts = [];
        foreach ($parts as $part) {
            if (! empty($part['thought'])) {
                continue;
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
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $text, $m)) {
            $text = trim($m[1]);
        }
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }
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
