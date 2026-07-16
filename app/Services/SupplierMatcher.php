<?php

namespace App\Services;

use App\Models\Supplier;

/**
 * Matches an extracted invoice's supplier (tax number + name) against the suppliers
 * master (Spec 002 FR-105). Tax-number match is authoritative; otherwise fuzzy
 * name matching suggests the most likely supplier. The pure scoring functions are
 * unit-testable without a DB.
 */
class SupplierMatcher
{
    /** Name-similarity threshold (0..1) above which a name match is "confident". */
    public const NAME_THRESHOLD = 0.86;

    /**
     * Resolve against the live suppliers table.
     *
     * @return array{match: ?Supplier, suggestions: array, reason: string}
     */
    public function match(?string $taxNumber, ?string $name): array
    {
        $tax = self::digits($taxNumber);
        if ($tax !== '') {
            $byTax = Supplier::where('tax_number', $tax)->first();
            if ($byTax) {
                return ['match' => $byTax, 'suggestions' => [], 'reason' => 'tax_number'];
            }
        }

        $name = trim((string) $name);
        if ($name === '') {
            return ['match' => null, 'suggestions' => [], 'reason' => 'no_name'];
        }

        $all = Supplier::select('id', 'name', 'tax_number')->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'tax_number' => $s->tax_number])->all();
        $ranked = self::rankByName($all, $name);

        if ($ranked && $ranked[0]['score'] >= self::NAME_THRESHOLD) {
            $best = Supplier::find($ranked[0]['id']);

            return ['match' => $best, 'suggestions' => array_slice($ranked, 0, 3), 'reason' => 'name'];
        }

        return ['match' => null, 'suggestions' => array_slice($ranked, 0, 3), 'reason' => 'suggest'];
    }

    /**
     * Pure: rank candidate suppliers by name similarity to $name, descending.
     * Each candidate is ['id'=>, 'name'=>, ...]; returns them with a 'score' added.
     *
     * @param  array<int, array>  $candidates
     * @return array<int, array>
     */
    public static function rankByName(array $candidates, string $name): array
    {
        $ranked = [];
        foreach ($candidates as $c) {
            $ranked[] = $c + ['score' => self::nameSimilarity($name, (string) ($c['name'] ?? ''))];
        }
        usort($ranked, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $ranked;
    }

    /** Pure: 0..1 similarity between two supplier names, layout/whitespace tolerant. */
    public static function nameSimilarity(string $a, string $b): float
    {
        $a = self::normName($a);
        $b = self::normName($b);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 1.0;
        }
        similar_text($a, $b, $pct);

        return round($pct / 100, 4);
    }

    /** Normalize a company name for comparison: collapse space, strip common noise words. */
    public static function normName(string $s): string
    {
        $s = trim(mb_strtolower($s));
        $s = preg_replace('/\s+/u', ' ', $s);
        // Drop very common company qualifiers that add noise in both languages.
        $noise = ['شركة', 'مؤسسة', 'للتجارة', 'التجارية', 'company', 'co', 'co.', 'est', 'llc', 'ltd', 'trading'];

        return trim(str_replace($noise, '', $s));
    }

    private static function digits(?string $v): string
    {
        return preg_replace('/\D+/', '', (string) $v);
    }
}
