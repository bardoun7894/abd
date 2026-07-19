<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Spec 004 B1 — reads an operating-expense receipt (image/PDF) via Gemini and returns
 * fields to PREFILL the existing expense screen: amount, date, vendor, a description,
 * and a suggested expense category (matched against the real `expense_categoty` table).
 * The user confirms in the normal expense form, which saves to the real `expense` table.
 */
class ExpenseAiExtractor
{
    public function __construct(private GeminiClient $gemini, private InteractiveDocPrep $docPrep) {}

    /**
     * @return array{expense_price:?float, vendor:?string, date:?string, description:?string,
     *               expense_categoty_id:?int, category_name:?string, field_confidence:array,
     *               _in:int, _out:int}
     */
    public function extract(string $filePath, ?string $model = null): array
    {
        // Interactive prefill — fast-fail budget so a slow model can't freeze the request.
        // Billing: only page 1, downscaled, is sent — see InteractiveDocPrep.
        $prep = $this->docPrep->prepare($filePath);
        try {
            $raw = $this->gemini->extractAdaptive($this->prompt(), $prep['path'], $this->schema(), $model, (int) config('services.gemini.interactive_timeout', 25), (int) config('services.gemini.interactive_retries', 2));
        } finally {
            ($prep['cleanup'])();
        }

        $amount = $this->num($raw['total_amount'] ?? null);
        $vendor = $this->cleanStr($raw['vendor_name'] ?? null);
        $date = $this->parseDate($raw['date'] ?? null);
        $desc = $this->cleanStr($raw['description'] ?? null);
        $catHint = $this->cleanStr($raw['category'] ?? null);

        [$catId, $catName] = $this->suggestCategory($catHint);

        return [
            'expense_price' => $amount,
            'vendor' => $vendor,
            'date' => $date,
            'description' => $desc,
            'expense_categoty_id' => $catId,
            'category_name' => $catName,
            'field_confidence' => $this->confidence($raw['field_confidence'] ?? null),
            '_in' => $this->gemini->lastInputTokens(),
            '_out' => $this->gemini->lastOutputTokens(),
            '_model' => $this->gemini->lastModel,
            '_escalated' => $this->gemini->lastEscalated,
        ];
    }

    /**
     * Match the model's free-text category hint to a real expense_categoty row by
     * name similarity. Returns [id|null, name|null]. Pure matching reuses SupplierMatcher.
     */
    public function suggestCategory(?string $hint): array
    {
        $hint = trim((string) $hint);
        if ($hint === '') {
            return [null, null];
        }
        $cats = DB::table('expense_categoty')->select('expense_categoty_id', 'expense_categoty_name')->get();
        $best = null;
        $bestScore = 0.0;
        foreach ($cats as $c) {
            $s = SupplierMatcher::nameSimilarity($hint, (string) $c->expense_categoty_name);
            if ($s > $bestScore) {
                $bestScore = $s;
                $best = $c;
            }
        }
        if ($best && $bestScore >= 0.55) {
            return [(int) $best->expense_categoty_id, $best->expense_categoty_name];
        }

        return [null, null];
    }

    private function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'vendor_name' => ['type' => 'STRING', 'nullable' => true],
                'total_amount' => ['type' => 'NUMBER', 'nullable' => true],
                'date' => ['type' => 'STRING', 'nullable' => true],
                'category' => ['type' => 'STRING', 'nullable' => true],
                'description' => ['type' => 'STRING', 'nullable' => true],
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'total_amount' => ['type' => 'NUMBER', 'nullable' => true],
                        'date' => ['type' => 'NUMBER', 'nullable' => true],
                        'vendor_name' => ['type' => 'NUMBER', 'nullable' => true],
                    ],
                ],
            ],
            'required' => ['vendor_name', 'total_amount', 'date'],
        ];
    }

    private function prompt(): string
    {
        return "أنت محرّك استخراج بيانات لإيصالات ومصاريف تشغيلية (عربي/إنجليزي). اقرأ الإيصال وأعد JSON فقط:\n"
            .'- vendor_name: اسم المتجر/المورد.'."\n"
            .'- total_amount: المبلغ الإجمالي المدفوع (رقم بدون رمز عملة، النقطة للكسور).'."\n"
            .'- date: تاريخ الإيصال بصيغة YYYY-MM-DD.'."\n"
            .'- category: نوع المصروف بكلمة أو كلمتين (مثل: وقود، صيانة، كهرباء، إيجار، قرطاسية، اتصالات).'."\n"
            .'- description: وصف مختصر لما تم شراؤه.'."\n"
            .'- field_confidence: درجة ثقة 0..1 لكل من total_amount و date و vendor_name.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن؛ استخدم null لأي حقل غير موجود. أعد JSON فقط.';
    }

    // ---- small pure normalizers ----
    private function num($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $v = str_replace(['٬', ',', ' ', 'ر.س', 'SAR', 'SR', 'ريال'], '', $this->arabicDigits((string) $v));

        return is_numeric($v) ? (float) $v : null;
    }

    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

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

    private function confidence($fc): array
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

    private function arabicDigits(string $s): string
    {
        return strtr($s, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    }
}
