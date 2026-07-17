<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Spec 005 T-B3 — Home AI insight card. Computes this month's key aggregates vs last
 * month in PHP (DB::table), sends ONLY those computed numbers to Gemini for a short
 * Arabic insight. The Gemini text itself is cached per calendar month (Cache::remember)
 * so it is NOT re-generated on every page load. Never throws on the home page — on any
 * Gemini failure the caller gets the raw deltas with summary=null/fallback=true and must
 * render the numbers instead of the narrative.
 *
 * Text-only Gemini calls are delegated to GeminiClient::generateText() so retry/backoff
 * logic lives in one place.
 */
class HomeInsightService
{
    /** Cache key for the current (or given) month's Gemini insight text. */
    public function cacheKey(?DateTimeInterface $at = null): string
    {
        $at = $at ?: now();

        return 'home_ai_insight_'.$at->format('Y_m');
    }

    /**
     * PURE — percentage delta helper.
     * previous=0 & current=0 => 0%; previous=0 & current>0 => 100% (can't divide by zero).
     */
    public function deltaPct(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current == 0.0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    /**
     * PURE — builds the current/previous/delta_pct shape for each metric from raw totals.
     *
     * @param  array{income?:float,expense?:float,purchase?:float}  $current
     * @param  array{income?:float,expense?:float,purchase?:float}  $previous
     */
    public function computeDeltas(array $current, array $previous): array
    {
        $metrics = [];
        foreach (['income', 'expense', 'purchase'] as $key) {
            $c = round((float) ($current[$key] ?? 0), 2);
            $p = round((float) ($previous[$key] ?? 0), 2);
            $metrics[$key] = [
                'current' => $c,
                'previous' => $p,
                'delta_pct' => $this->deltaPct($c, $p),
            ];
        }

        return $metrics;
    }

    /** DB aggregates for the current + previous calendar month. Not unit-testable without a DB. */
    public function monthlyDeltas(): array
    {
        $thisStart = now()->startOfMonth()->toDateString();
        $thisEnd = now()->endOfMonth()->toDateString();
        $prevStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $prevEnd = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $sum = function (string $table, string $column, string $from, string $to, bool $filterDeleted) {
            $q = DB::table($table)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
            if ($filterDeleted) {
                $q->where('is_deleted', 0);
            }

            return (float) $q->sum($column);
        };

        $current = [
            'income' => $sum('financial', 'financial_month_val', $thisStart, $thisEnd, true),
            'expense' => $sum('expense', 'expense_price', $thisStart, $thisEnd, true),
            'purchase' => $sum('purchase', 'purchase_price', $thisStart, $thisEnd, false),
        ];
        $previous = [
            'income' => $sum('financial', 'financial_month_val', $prevStart, $prevEnd, true),
            'expense' => $sum('expense', 'expense_price', $prevStart, $prevEnd, true),
            'purchase' => $sum('purchase', 'purchase_price', $prevStart, $prevEnd, false),
        ];

        return $this->computeDeltas($current, $previous);
    }

    /** PURE — prompt shape. Only computed deltas are sent, never raw rows. */
    public function buildPrompt(array $deltas): string
    {
        return "أنت محلل مالي مختصر. لديك أرقام هذا الشهر مقارنة بالشهر الماضي بصيغة JSON فقط (لا تخترع أرقاماً أخرى):\n"
            .json_encode($deltas, JSON_UNESCAPED_UNICODE)."\n"
            .'اكتب ملاحظة تحليلية بالعربية من جملتين إلى ثلاث جمل تلخص أهم تغيّر لصاحب العمل. أجب بنص عادي فقط دون أي تنسيق JSON.';
    }

    /**
     * @return array{summary:?string, deltas:array, fallback:bool}
     */
    public function insight(?string $model = null): array
    {
        $deltas = $this->monthlyDeltas();
        $summary = null;
        $fallback = false;

        try {
            $summary = Cache::remember($this->cacheKey(), now()->addHours(6), function () use ($deltas, $model) {
                return trim(app(GeminiClient::class)->generateText($this->buildPrompt($deltas), $model));
            });
        } catch (Throwable $e) {
            // Graceful fallback — the home page must never break because Gemini is down.
            $fallback = true;
        }

        return ['summary' => $summary, 'deltas' => $deltas, 'fallback' => $fallback];
    }
}
