<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Spec 004 B3 — reads a Saudi Iqama / passport / national ID scan (image or PDF) via
 * Gemini and returns fields to PREFILL the existing worker-add screen: worker_name,
 * ssn (iqama / national ID number), passport_no, dob (date of birth), doe (iqama
 * expiry), dop (passport expiry), and a suggested nationality (matched against the
 * real `nation` table). Nothing is saved here — the user confirms in the normal
 * worker form, which writes to the real `workers` table.
 */
class WorkerAiExtractor
{
    public function __construct(private GeminiClient $gemini) {}

    /**
     * @return array{worker_name:?string, ssn:?string, passport_no:?string, dob:?string,
     *               doe:?string, dop:?string, nation_id:?int, nationality_name:?string,
     *               field_confidence:array, _in:int, _out:int}
     */
    public function extract(string $filePath, ?string $model = null): array
    {
        $raw = $this->gemini->extract($this->prompt(), $filePath, $this->schema(), $model);

        $workerName = $this->cleanStr($raw['worker_name'] ?? null);
        $ssn = $this->idStr($raw['ssn'] ?? null);
        $passportNo = $this->idStr($raw['passport_no'] ?? null);
        $dob = $this->parseDate($raw['dob'] ?? null);
        $doe = $this->parseDate($raw['doe'] ?? null);
        $dop = $this->parseDate($raw['dop'] ?? null);
        $nationHint = $this->cleanStr($raw['nationality'] ?? null);

        [$nationId, $nationName] = $this->suggestNation($nationHint);

        return [
            'worker_name' => $workerName,
            'ssn' => $ssn,
            'passport_no' => $passportNo,
            'dob' => $dob,
            'doe' => $doe,
            'dop' => $dop,
            'nation_id' => $nationId,
            'nationality_name' => $nationName,
            'field_confidence' => $this->confidence($raw['field_confidence'] ?? null),
            '_in' => $this->gemini->lastInputTokens(),
            '_out' => $this->gemini->lastOutputTokens(),
        ];
    }

    /**
     * Match the model's free-text nationality hint (Arabic or English) to a real
     * `nation` row by name similarity. Returns [id|null, name_ar|null].
     */
    public function suggestNation(?string $hint): array
    {
        $hint = trim((string) $hint);
        if ($hint === '') {
            return [null, null];
        }
        $nations = DB::table('nation')->select('nation_id', 'nation_name_ar', 'nation_name_en')->get();
        $best = null;
        $bestScore = 0.0;
        foreach ($nations as $n) {
            $sAr = SupplierMatcher::nameSimilarity($hint, (string) $n->nation_name_ar);
            $sEn = SupplierMatcher::nameSimilarity($hint, (string) $n->nation_name_en);
            $s = max($sAr, $sEn);
            if ($s > $bestScore) {
                $bestScore = $s;
                $best = $n;
            }
        }
        if ($best && $bestScore >= 0.55) {
            return [(int) $best->nation_id, $best->nation_name_ar];
        }

        return [null, null];
    }

    private function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'worker_name' => ['type' => 'STRING', 'nullable' => true],
                'ssn' => ['type' => 'STRING', 'nullable' => true],
                'passport_no' => ['type' => 'STRING', 'nullable' => true],
                'dob' => ['type' => 'STRING', 'nullable' => true],
                'doe' => ['type' => 'STRING', 'nullable' => true],
                'dop' => ['type' => 'STRING', 'nullable' => true],
                'nationality' => ['type' => 'STRING', 'nullable' => true],
                'field_confidence' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'worker_name' => ['type' => 'NUMBER', 'nullable' => true],
                        'ssn' => ['type' => 'NUMBER', 'nullable' => true],
                        'passport_no' => ['type' => 'NUMBER', 'nullable' => true],
                        'dob' => ['type' => 'NUMBER', 'nullable' => true],
                        'doe' => ['type' => 'NUMBER', 'nullable' => true],
                        'dop' => ['type' => 'NUMBER', 'nullable' => true],
                    ],
                ],
            ],
            'required' => ['worker_name', 'ssn'],
        ];
    }

    private function prompt(): string
    {
        return "أنت محرّك استخراج بيانات لوثائق هوية العمال (إقامة سعودية / جواز سفر / هوية وطنية) عربي/إنجليزي. اقرأ المستند وأعد JSON فقط:\n"
            .'- worker_name: اسم العامل كما هو مكتوب في الوثيقة.'."\n"
            .'- ssn: رقم الإقامة (للمقيمين) أو رقم الهوية الوطنية (للسعوديين).'."\n"
            .'- passport_no: رقم جواز السفر.'."\n"
            .'- dob: تاريخ الميلاد بصيغة YYYY-MM-DD.'."\n"
            .'- doe: تاريخ انتهاء الإقامة بصيغة YYYY-MM-DD.'."\n"
            .'- dop: تاريخ انتهاء جواز السفر بصيغة YYYY-MM-DD.'."\n"
            .'- nationality: الجنسية بكلمة أو كلمتين (عربي أو إنجليزي).'."\n"
            .'- field_confidence: درجة ثقة 0..1 لكل حقل، خصوصاً ssn و passport_no و dob و doe و dop.'."\n"
            .'حوّل الأرقام العربية إلى لاتينية. لا تخمّن رقم الإقامة أو رقم الجواز؛ إن كانت القراءة غير واضحة أعد null وخفّض قيمة field_confidence بدلاً من اختراع رقم. استخدم null لأي حقل غير موجود إطلاقاً. أعد JSON فقط.';
    }

    // ---- small pure normalizers ----
    private function cleanStr($v): ?string
    {
        $v = trim((string) ($v ?? ''));

        return $v === '' ? null : $v;
    }

    /** Normalize an ID-like string (ssn/passport_no): Arabic digits -> Latin, trim, keep as string. */
    private function idStr($v): ?string
    {
        $v = trim($this->arabicDigits((string) ($v ?? '')));

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
