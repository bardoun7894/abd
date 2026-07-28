<?php

namespace App\Support;

/**
 * Arabic text folding for COMPARISON only.
 *
 * The same word reaches us spelled differently depending on who typed it and
 * which page of a PDF it came from — final ya as ى or ي, ta marbuta as ة or ه,
 * hamza carried or bare, harakat present or absent. Matching such text with ===
 * makes correctness depend on orthography.
 *
 * Never store or display the folded form: it is lossy, and showing it back to a
 * user misquotes the document they are reading.
 */
class ArabicText
{
    private const LETTER_MAP = [
        'ى' => 'ي',                         // alef maqsura → ya
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', // hamzated alef → bare alef
        'ة' => 'ه',                         // ta marbuta → ha
        'ؤ' => 'و', 'ئ' => 'ي',
        'ـ' => '',                          // tatweel
    ];

    /** Lowercased, harakat-stripped, letter-folded, whitespace-collapsed. */
    public static function fold(?string $s): string
    {
        if ($s === null) {
            return '';
        }

        $s = strtr(trim($s), self::LETTER_MAP);
        $s = preg_replace('/[\x{064B}-\x{0652}]/u', '', $s) ?? $s;   // harakat
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return mb_strtolower($s);
    }
}
