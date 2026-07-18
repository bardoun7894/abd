<?php

namespace App\Services;

/**
 * Spec 005 remaining-work — reads a group-leader ID / passport scan (image or PDF) via
 * Gemini and returns fields to PREFILL the existing manager-add screen: manager_name
 * and manager_mobile (the only two fields the manager add/edit forms actually expose —
 * see resources/views/dashboard/manager/index.blade.php and upd_manager.blade.php).
 * Nothing is saved here — the user confirms in the normal manager form, which writes
 * to the real `manager` table. Mirrors App\Services\WorkerAiExtractor.
 */
class ManagerAiExtractor
{
    public function __construct(private GeminiClient $gemini) {}

    /**
     * @return array{manager_name:?string, manager_mobile:?string, field_confidence:array, _in:int, _out:int}
     */
    public function extract(string $filePath, ?string $model = null): array
    {
        // Interactive prefill — fast-fail budget so a slow model can't freeze the request.
        $raw = $this->gemini->extract($this->prompt(), $filePath, $this->schema(), $model, null, (int) config('services.gemini.interactive_timeout', 25), (int) config('services.gemini.interactive_retries', 2));

        $managerName = $this->cleanStr($raw['manager_name'] ?? null);
        $managerMobile = $this->idStr($raw['manager_mobile'] ?? null);

        return [
            'manager_name' => $managerName,
            'manager_mobile' => $managerMobile,
            'field_confidence' => $this->confidence($raw['field_confidence'] ?? null),
            '_in' => $this->gemini->lastInputTokens(),
            '_out' => $this->gemini->lastOutputTokens(),
        ];
    }

    private function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'manager_name' => ['type' => 'STRING', 'nullable' => true],
                'manager_mobile' => ['type' => 'STRING', 'nullable' => true],
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'manager_name' => ['type' => 'NUMBER', 'nullable' => true],
                        'manager_mobile' => ['type' => 'NUMBER', 'nullable' => true],
                    ],
                ],
            ],
            'required' => ['manager_name'],
        ];
    }

    private function prompt(): string
    {
        return "أنت محرّك استخراج بيانات لوثائق هوية قائد المجموعة (إقامة سعودية / جواز سفر / هوية وطنية) عربي/إنجليزي. اقرأ المستند وأعد JSON فقط:\n"
            .'- manager_name: اسم قائد المجموعة كما هو مكتوب في الوثيقة.'."\n"
            .'- manager_mobile: رقم الجوال إن وُجد مطبوعاً على الوثيقة، وإلا أعد null.'."\n"
            .'- field_confidence: درجة ثقة 0..1 لكل حقل.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخترع اسماً أو رقم جوال؛ إن كانت القراءة غير واضحة أعد null وخفّض قيمة field_confidence بدلاً من التخمين. استخدم null لأي حقل غير موجود إطلاقاً. أعد JSON فقط.';
    }

    // ---- small pure normalizers (mirrors WorkerAiExtractor) ----
    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    /** Normalize an ID/mobile-like string: Arabic digits -> Latin, trim, keep as string. */
    private function idStr($v): ?string
    {
        $v = trim($this->arabicDigits((string) ($v ?? '')));

        return $v === '' ? null : $v;
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
