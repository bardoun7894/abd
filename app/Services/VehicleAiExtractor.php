<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Spec 004 B4 — reads a vehicle document (استمارة/رخصة سير, insurance, operating card;
 * image/PDF) via Gemini and returns fields to PREFILL the existing vehicle add/edit
 * screen: plate number, expiry dates, and owner/make-model if present. Nothing is
 * saved here — the user confirms in the normal vehicle form, which writes to the real
 * `vehicles` table.
 */
class VehicleAiExtractor
{
    public function __construct(private GeminiClient $gemini) {}

    /**
     * @return array{plate_number:?string, owner_name:?string, model:?string,
     *               license_expiry:?string, insurance_expiry:?string, operation_card_expiry:?string,
     *               field_confidence:array, _in:int, _out:int}
     */
    public function extract(string $filePath, ?string $model = null): array
    {
        $raw = $this->gemini->extract($this->prompt(), $filePath, $this->schema(), $model);

        return [
            'plate_number' => $this->cleanPlate($raw['plate_number'] ?? null),
            'owner_name' => $this->cleanStr($raw['owner_name'] ?? null),
            'model' => $this->cleanStr($raw['model'] ?? null),
            'license_expiry' => $this->parseDate($raw['license_expiry'] ?? null),
            'insurance_expiry' => $this->parseDate($raw['insurance_expiry'] ?? null),
            'operation_card_expiry' => $this->parseDate($raw['operation_card_expiry'] ?? null),
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
                'plate_number' => ['type' => 'STRING', 'nullable' => true],
                'owner_name' => ['type' => 'STRING', 'nullable' => true],
                'model' => ['type' => 'STRING', 'nullable' => true],
                'license_expiry' => ['type' => 'STRING', 'nullable' => true],
                'insurance_expiry' => ['type' => 'STRING', 'nullable' => true],
                'operation_card_expiry' => ['type' => 'STRING', 'nullable' => true],
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'plate_number' => ['type' => 'NUMBER', 'nullable' => true],
                        'license_expiry' => ['type' => 'NUMBER', 'nullable' => true],
                        'insurance_expiry' => ['type' => 'NUMBER', 'nullable' => true],
                        'operation_card_expiry' => ['type' => 'NUMBER', 'nullable' => true],
                    ],
                ],
            ],
            'required' => ['plate_number', 'license_expiry', 'insurance_expiry', 'operation_card_expiry'],
        ];
    }

    private function prompt(): string
    {
        return "أنت محرّك استخراج بيانات لوثائق مركبات (استمارة/رخصة سير، تأمين، كرت تشغيل — عربي/إنجليزي). اقرأ الوثيقة وأعد JSON فقط:\n"
            .'- plate_number: رقم لوحة المركبة كما هو مكتوب.'."\n"
            .'- owner_name: اسم مالك المركبة إن وُجد.'."\n"
            .'- model: نوع/موديل المركبة إن وُجد (مثل: تويوتا كامري 2022).'."\n"
            .'- license_expiry: تاريخ انتهاء رخصة السير بصيغة YYYY-MM-DD.'."\n"
            .'- insurance_expiry: تاريخ انتهاء التأمين بصيغة YYYY-MM-DD.'."\n"
            .'- operation_card_expiry: تاريخ انتهاء كرت التشغيل بصيغة YYYY-MM-DD.'."\n"
            .'- field_confidence: درجة ثقة 0..1 لكل من plate_number و license_expiry و insurance_expiry و operation_card_expiry.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن؛ استخدم null لأي حقل غير موجود في الوثيقة. أعد JSON فقط.';
    }

    // ---- small pure normalizers ----
    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    private function cleanPlate($v): ?string
    {
        $v = $this->cleanStr($v);
        if ($v === null) {
            return null;
        }

        return $this->arabicDigits($v);
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
