<?php

namespace App\Support;

/**
 * تفقيط — renders an amount as Arabic words for the سند قبض/صرف voucher, the way
 * a printed financial document is expected to carry it ("فقط عشرون ألف ريال لا غير").
 *
 * Written by hand rather than pulled in as a dependency because Arabic numerals
 * have grammar a generic library gets wrong:
 *   - 1 and 2 are expressed by the noun itself, not by a counted number
 *     (ألف / ألفان, مئة / مئتان) — never "واحد ألف".
 *   - 3-10 take the plural (ثلاثة آلاف), 11+ take the singular (أحد عشر ألفاً).
 *   - Compounds are joined with و in descending order (ألف وتسعمئة وخمسة).
 *
 * Scope: 0 to 999,999,999.99, which is far beyond any rent installment this app
 * handles. Anything larger falls back to the plain formatted number so a printed
 * voucher can never show a wrong figure in words.
 */
class ArabicNumberToWords
{
    private const ONES = [
        '', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة',
        'ستة', 'سبعة', 'ثمانية', 'تسعة', 'عشرة',
        'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر',
        'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر',
    ];

    private const TENS = [
        20 => 'عشرون', 30 => 'ثلاثون', 40 => 'أربعون', 50 => 'خمسون',
        60 => 'ستون', 70 => 'سبعون', 80 => 'ثمانون', 90 => 'تسعون',
    ];

    private const HUNDREDS = [
        100 => 'مئة', 200 => 'مئتان', 300 => 'ثلاثمئة', 400 => 'أربعمئة',
        500 => 'خمسمئة', 600 => 'ستمئة', 700 => 'سبعمئة', 800 => 'ثمانمئة',
        900 => 'تسعمئة',
    ];

    /**
     * [singular, dual, plural-3-10, singular-for-11+] per scale.
     *
     * The 11+ form is the bare singular ("عشرون ألف ريال"), not the accusative
     * "ألفاً". Standing alone the accusative is the textbook form, but on a
     * voucher the scale word is immediately followed by the currency, where
     * every Saudi/Gulf تفقيط prints the construct form.
     */
    private const SCALES = [
        1000 => ['ألف', 'ألفان', 'آلاف', 'ألف'],
        1000000 => ['مليون', 'مليونان', 'ملايين', 'مليون'],
    ];

    private const MAX = 999999999.99;

    /**
     * Full voucher phrase: "فقط <words> ريال[ و<words> هللة] لا غير".
     * Returns null when the amount is out of range or not a number, so the
     * caller can simply omit the line instead of printing something wrong.
     */
    public static function amount(float|int|string|null $value, string $currency = 'ريال', string $fraction = 'هللة'): ?string
    {
        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        $value = round((float) $value, 2);
        if ($value < 0 || $value > self::MAX) {
            return null;
        }

        $whole = (int) floor($value);
        // String maths on the rounded value avoids the classic 0.1+0.2 drift
        // turning 20.10 into "تسع هللات".
        $cents = (int) round(($value - $whole) * 100);

        $parts = [];
        if ($whole > 0 || $cents === 0) {
            $parts[] = self::integer($whole).' '.$currency;
        }
        if ($cents > 0) {
            $parts[] = self::integer($cents).' '.$fraction;
        }

        return 'فقط '.implode(' و', $parts).' لا غير';
    }

    /** Bare words for a non-negative integer, no currency, no "فقط". */
    public static function integer(int $n): string
    {
        if ($n === 0) {
            return 'صفر';
        }

        $chunks = [];
        foreach ([1000000, 1000] as $scale) {
            $count = intdiv($n, $scale);
            if ($count > 0) {
                $chunks[] = self::scaleWords($count, $scale);
                $n %= $scale;
            }
        }
        if ($n > 0) {
            $chunks[] = self::belowThousand($n);
        }

        return implode(' و', array_filter($chunks));
    }

    /** "ألف" / "ألفان" / "ثلاثة آلاف" / "أحد عشر ألفاً" — the grammar that matters. */
    private static function scaleWords(int $count, int $scale): string
    {
        [$one, $two, $plural, $singularAfterTen] = self::SCALES[$scale];

        if ($count === 1) {
            return $one;
        }
        if ($count === 2) {
            return $two;
        }
        if ($count <= 10) {
            return self::belowThousand($count).' '.$plural;
        }

        return self::belowThousand($count).' '.$singularAfterTen;
    }

    private static function belowThousand(int $n): string
    {
        $words = [];

        $hundreds = intdiv($n, 100) * 100;
        if ($hundreds > 0) {
            $words[] = self::HUNDREDS[$hundreds];
            $n -= $hundreds;
        }

        if ($n > 0) {
            if ($n < 20) {
                $words[] = self::ONES[$n];
            } else {
                $tens = intdiv($n, 10) * 10;
                $ones = $n % 10;
                // Arabic says the unit BEFORE the ten: خمسة وعشرون.
                $words[] = $ones > 0
                    ? self::ONES[$ones].' و'.self::TENS[$tens]
                    : self::TENS[$tens];
            }
        }

        return implode(' و', $words);
    }
}
