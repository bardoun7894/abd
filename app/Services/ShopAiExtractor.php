<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Spec 004 B2 — reads a scanned shop document (commercial registration, municipal
 * license, or lease/rent contract; image/PDF) via Gemini and returns fields to
 * PREFILL the existing shop document screen: which document it is, its number,
 * issue + expiry dates, the owner/establishment (or landlord) name, and — for a
 * lease — the rent amount. The user confirms in the normal shop document form
 * (upd_file.blade.php), which writes to the real shop_comme / shop_municip /
 * shop_rent tables.
 */
class ShopAiExtractor
{
    public function __construct(private GeminiClient $gemini, private InteractiveDocPrep $docPrep) {}

    /**
     * @return array{document_type:?string, document_number:?string, issue_date:?string,
     *               expiry_date:?string, owner_name:?string, rent_amount:?float,
     *               num_payments:?int, payment_value:?float, payment_frequency:?string,
     *               field_confidence:array, _in:int, _out:int}
     */
    public function extract(string $filePath, ?string $model = null): array
    {
        // Interactive path (synchronous AJAX prefill): fast-fail budget so a slow or
        // overloaded model returns an error in ~40s instead of blocking the request
        // (and a PHP-FPM worker) for minutes. Background pipelines don't pass these.
        // Billing: only the leading pages, downscaled, are sent — see InteractiveDocPrep.
        // EJAR unified leases keep the financial block (annual rent, number of
        // payments, payment cycle) on page 3 — page-1-only misses the rent schedule.
        $prep = $this->docPrep->prepare($filePath, (int) config('services.gemini.interactive_pages', 3));
        try {
            $raw = $this->gemini->extractAdaptive(
                $this->prompt(),
                $prep['paths'],
                $this->schema(),
                $model,
                (int) config('services.gemini.interactive_timeout', 40),
                (int) config('services.gemini.interactive_retries', 2),
            );
        } finally {
            ($prep['cleanup'])();
        }

        return [
            'document_type' => $this->normalizeType($raw['document_type'] ?? null),
            'document_number' => $this->cleanIdStr($raw['document_number'] ?? null),
            'issue_date' => $this->parseDate($raw['issue_date'] ?? null),
            'expiry_date' => $this->parseDate($raw['expiry_date'] ?? null),
            'owner_name' => $this->cleanStr($raw['owner_name'] ?? null),
            'owner_mobile' => $this->cleanIdStr($raw['owner_mobile'] ?? null),
            'rent_amount' => $this->num($raw['rent_amount'] ?? null),
            // Lease payment-schedule inputs (client feedback 2026-07) — used to
            // generate the shop_rentpay دفعات automatically. Null for non-lease docs.
            'num_payments' => $this->intOrNull($raw['num_payments'] ?? null),
            'payment_value' => $this->num($raw['payment_value'] ?? null),
            'payment_frequency' => $this->cleanStr($raw['payment_frequency'] ?? null),
            // Tenant/business block found inside EJAR leases — lets one lease upload
            // also fill the commercial-registration section (same fields, one file).
            'unified_number' => $this->cleanIdStr($raw['unified_number'] ?? null),
            'tenant_cr_number' => $this->cleanIdStr($raw['tenant_cr_number'] ?? null),
            'tenant_cr_date' => $this->parseDate($raw['tenant_cr_date'] ?? null),
            'city' => $this->cleanStr($raw['city'] ?? null),
            'shop_area' => $this->num($raw['shop_area'] ?? null),
            'field_confidence' => $this->confidence($raw['field_confidence'] ?? null),
            '_in' => $this->gemini->lastInputTokens(),
            '_out' => $this->gemini->lastOutputTokens(),
            '_model' => $this->gemini->lastModel,
            '_escalated' => $this->gemini->lastEscalated,
        ];
    }

    private function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'document_type' => [
                    'type' => 'STRING',
                    'nullable' => true,
                    'enum' => ['commercial_registration', 'municipal_license', 'lease'],
                ],
                'document_number' => ['type' => 'STRING', 'nullable' => true],
                'issue_date' => ['type' => 'STRING', 'nullable' => true],
                'expiry_date' => ['type' => 'STRING', 'nullable' => true],
                'owner_name' => ['type' => 'STRING', 'nullable' => true],
                'owner_mobile' => ['type' => 'STRING', 'nullable' => true],
                'rent_amount' => ['type' => 'NUMBER', 'nullable' => true],
                'num_payments' => ['type' => 'INTEGER', 'nullable' => true],
                'payment_value' => ['type' => 'NUMBER', 'nullable' => true],
                'payment_frequency' => ['type' => 'STRING', 'nullable' => true],
                'unified_number' => ['type' => 'STRING', 'nullable' => true],
                'tenant_cr_number' => ['type' => 'STRING', 'nullable' => true],
                'tenant_cr_date' => ['type' => 'STRING', 'nullable' => true],
                'city' => ['type' => 'STRING', 'nullable' => true],
                'shop_area' => ['type' => 'NUMBER', 'nullable' => true],
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'document_number' => ['type' => 'NUMBER', 'nullable' => true],
                        'issue_date' => ['type' => 'NUMBER', 'nullable' => true],
                        'expiry_date' => ['type' => 'NUMBER', 'nullable' => true],
                        'owner_name' => ['type' => 'NUMBER', 'nullable' => true],
                    ],
                ],
            ],
            'required' => ['document_type', 'document_number', 'expiry_date'],
        ];
    }

    private function prompt(): string
    {
        return "أنت محرّك استخراج بيانات لمستندات محل تجاري (عربي/إنجليزي): السجل التجاري أو رخصة البلدية أو عقد الإيجار. قد يصلك المستند كعدة صفحات (عقود إيجار إيجار/REGA الموحّدة تضع البيانات المالية في الصفحة 2-3 عادةً) — اقرأ كل الصفحات وأعد JSON فقط:\n"
            .'- document_type: نوع المستند، واحد فقط من: commercial_registration (السجل التجاري) أو municipal_license (رخصة البلدية) أو lease (عقد إيجار).'."\n"
            .'- document_number: رقم السجل التجاري أو رقم الرخصة أو رقم عقد الإيجار حسب نوع المستند.'."\n"
            .'- issue_date: تاريخ الإصدار/البداية بصيغة YYYY-MM-DD.'."\n"
            .'- expiry_date: تاريخ الانتهاء بصيغة YYYY-MM-DD.'."\n"
            .'- owner_name: اسم صاحب المؤسسة/المحل، أو اسم المالك/المؤجر في حال كان عقد إيجار.'."\n"
            .'- owner_mobile: رقم جوال المالك/المؤجر بصيغة دولية إن وُجد (مثال 9665xxxxxxxx) — فقط لعقد الإيجار، وإلا null.'."\n"
            .'- rent_amount: القيمة السنوية للإيجار (رقم بدون رمز عملة) — فقط إن كان المستند عقد إيجار، وإلا null.'."\n"
            .'- num_payments: عدد دفعات الإيجار كاملة (رقم صحيح) — فقط لعقد الإيجار، وإلا null.'."\n"
            .'- payment_value: قيمة الدفعة الواحدة (رقم بدون رمز عملة؛ إن لم تُذكر صراحةً احسبها: الإيجار السنوي × عدد سنوات العقد ÷ عدد الدفعات) — فقط لعقد الإيجار، وإلا null.'."\n"
            .'- payment_frequency: دورية سداد الإيجار (شهري/ربع سنوي/نصف سنوي/سنوي/دفعة واحدة) — فقط لعقد الإيجار، وإلا null.'."\n"
            .'- unified_number: الرقم الموحّد للمنشأة (المستأجر) إن وُجد في العقد، وإلا null.'."\n"
            .'- tenant_cr_number: رقم السجل التجاري للمستأجر كما يظهر داخل عقد الإيجار، وإلا null.'."\n"
            .'- tenant_cr_date: تاريخ سجل المستأجر التجاري بصيغة YYYY-MM-DD، وإلا null.'."\n"
            .'- city: مدينة العقار/المحل (مثال: الدمام، الرياض)، وإلا null.'."\n"
            .'- shop_area: مساحة الوحدة/المحل بالمتر المربع (رقم)، وإلا null.'."\n"
            .'- field_confidence: درجة ثقة 0..1 لكل من document_number و issue_date و expiry_date و owner_name.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن؛ استخدم null لأي حقل غير موجود. أعد JSON فقط.';
    }

    // ---- small pure normalizers ----
    private function normalizeType($v): ?string
    {
        $v = trim((string) ($v ?? ''));
        if (in_array($v, ['commercial_registration', 'municipal_license', 'lease'], true)) {
            return $v;
        }

        return $v === '' ? null : $v;
    }

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

    private function intOrNull($v): ?int
    {
        $n = $this->num($v);

        return $n === null ? null : (int) round($n);
    }

    private function cleanIdStr($v): ?string
    {
        $v = trim($this->arabicDigits((string) ($v ?? ''))) ;

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
