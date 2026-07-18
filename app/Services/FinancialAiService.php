<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Spec 005 remaining-work — Financial module AI: collection history, a short-term
 * forecast, deterministic anomaly flags, and an Arabic AI narrative.
 *
 * SCHEMA (confirmed against the live DB, not assumed):
 *  - `financial` — one row per worker per month: worker_id, financial_month_y,
 *    financial_month_m, financial_month_val (the amount DUE that month), is_deleted.
 *    There is NO `financial_month` table in this DB (a dead code path in
 *    FinancialController still references it) and `financial_detail` has no _m/_y
 *    columns — so the monthly bucket key always comes from `financial`.
 *  - `financial_detail` — one row per PAYMENT installment against a `financial`
 *    record: financial_id (FK), financial_month_pay (amount paid in that
 *    installment), financial_month_remain (running remainder snapshot).
 *
 * SAFETY MODEL (same as LeaseForecastService / HomeInsightService): every number is
 * computed here in PHP via DB::table aggregates over a fixed table/column set — the
 * model NEVER sees raw rows and NEVER generates SQL. Gemini only receives the
 * already-computed monthly due/paid/rate numbers (as JSON text) and is asked to
 * phrase an Arabic narrative around them.
 *
 * The bucketing/rate/anomaly math (buildHistory/buildForecast/detectAnomalies/
 * historicalCollectionRate/averageDue) is PURE — no DB, no container — so it's
 * unit-testable in isolation. The public collectionHistory()/forecast()/anomalies()
 * methods are thin DB-fetching wrappers around it, mirroring LeaseForecastService's
 * pure-core / DB-wrapper split. Kept table-name-only (no worker/manager scoping) so
 * Calculate/Accountings can reuse the pure core later against their own rows.
 */
class FinancialAiService
{
    /** A month's due is flagged when it exceeds this multiple of the running average of prior months. */
    private const DUE_SPIKE_MULTIPLIER = 1.5;

    /** A drop in collection rate (percentage points) between consecutive months is flagged above this threshold. */
    private const RATE_DROP_THRESHOLD = 30.0;

    // ---- public DB-fetching wrappers ----

    /**
     * Due vs paid per past calendar month, for the last N months.
     *
     * @return array<int, array{month:string, due:float, paid:float, remaining:float, rate:float}>
     */
    public function collectionHistory(int $months = 6): array
    {
        $now = Carbon::now()->startOfMonth();
        $nowIndex = $now->year * 12 + $now->month;
        $fromIndex = $nowIndex - $months;
        $toIndex = $nowIndex - 1;

        $rows = $fromIndex <= $toIndex ? $this->fetchFinancialRows($fromIndex, $toIndex) : [];

        return $this->buildHistory($rows, $months, $now);
    }

    /**
     * Next-N-months projected due/expected-collection, extrapolated from the average
     * due of the last N historical months weighted by the historical collection rate.
     *
     * @return array<int, array{month:string, due:float, expected:float}>
     */
    public function forecast(int $months = 6): array
    {
        $now = Carbon::now()->startOfMonth();
        $history = $this->collectionHistory($months);
        $rate = $this->historicalCollectionRate($history);
        $avgDue = $this->averageDue($history);

        return $this->buildForecast($avgDue, $rate, $months, $now);
    }

    /**
     * Deterministic, rule-based Arabic anomaly notes over the last N months of
     * collection history. No AI calls — pure rules over collectionHistory().
     *
     * @return array<int, string>
     */
    public function anomalies(int $months = 6): array
    {
        return $this->detectAnomalies($this->collectionHistory($months));
    }

    /**
     * Short Arabic narrative of the collection trend, phrased by Gemini around the
     * pre-computed collectionHistory() numbers. Cached per calendar month so Gemini is
     * never re-called on every page load. Falls back to the raw numbers (no
     * narrative) if Gemini is unavailable or fails — never blocks the Financial page.
     *
     * @param  array|null  $history  inject pre-computed history (tests); defaults to collectionHistory(6)
     * @return array{narrative:?string, history:array, source:string, error?:string}
     */
    public function narrative(?string $model = null, ?array $history = null): array
    {
        $history = $history ?? $this->collectionHistory(6);

        try {
            $narrative = Cache::remember($this->cacheKey(), now()->addHours(6), function () use ($history, $model) {
                return trim(app(GeminiClient::class)->generateText($this->narrativePrompt($history), $model));
            });

            return ['narrative' => $narrative, 'history' => $history, 'source' => 'ai'];
        } catch (\Throwable $e) {
            return ['narrative' => null, 'history' => $history, 'source' => 'fallback', 'error' => $e->getMessage()];
        }
    }

    /** Cache key for the current (or given) month's Gemini narrative text. */
    public function cacheKey(?Carbon $at = null): string
    {
        $at = $at ?: Carbon::now();

        return 'financial_ai_narrative_'.$at->format('Y_m');
    }

    // ---- pure core (unit tested directly, no DB) ----

    /**
     * PURE. Paid ÷ due ratio across the given history rows. Defaults to 1.0
     * (best-guess full collection) when there is no history at all, so an empty
     * history never zeroes out the forecast.
     *
     * @param  iterable<array{due:float,paid:float}>  $history
     */
    public function historicalCollectionRate(iterable $history): float
    {
        $due = 0.0;
        $paid = 0.0;
        foreach ($history as $h) {
            $due += (float) ($h['due'] ?? 0);
            $paid += (float) ($h['paid'] ?? 0);
        }

        return $due > 0 ? round($paid / $due, 4) : 1.0;
    }

    /**
     * PURE. Average due across the given history rows. 0.0 on empty history.
     *
     * @param  iterable<array{due:float}>  $history
     */
    public function averageDue(iterable $history): float
    {
        $rows = is_array($history) ? $history : iterator_to_array($history);
        if (empty($rows)) {
            return 0.0;
        }

        return round(array_sum(array_column($rows, 'due')) / count($rows), 2);
    }

    /**
     * PURE. Buckets due (financial.financial_month_val) vs paid
     * (Σ financial_detail.financial_month_pay) into $months calendar months ending
     * at (and including) the month before $now.
     *
     * @param  iterable<array{y:int,m:int,val:float,paid:float}>  $rows  one row per `financial` record
     * @return array<int, array{month:string, due:float, paid:float, remaining:float, rate:float}>
     */
    public function buildHistory(iterable $rows, int $months, ?Carbon $now = null): array
    {
        $now = ($now ?: Carbon::now())->copy()->startOfMonth();
        $buckets = [];
        for ($i = $months; $i >= 1; $i--) {
            $buckets[$now->copy()->subMonths($i)->format('Y-m')] = ['due' => 0.0, 'paid' => 0.0];
        }

        foreach ($rows as $r) {
            $y = (int) ($r['y'] ?? 0);
            $m = (int) ($r['m'] ?? 0);
            if ($y <= 0 || $m <= 0) {
                continue;
            }
            $key = sprintf('%04d-%02d', $y, $m);
            if (! array_key_exists($key, $buckets)) {
                continue;
            }
            $buckets[$key]['due'] += (float) ($r['val'] ?? 0);
            $buckets[$key]['paid'] += (float) ($r['paid'] ?? 0);
        }

        $out = [];
        foreach ($buckets as $month => $b) {
            $out[] = [
                'month' => $month,
                'due' => round($b['due'], 2),
                'paid' => round($b['paid'], 2),
                'remaining' => round($b['due'] - $b['paid'], 2),
                'rate' => $b['due'] > 0 ? round($b['paid'] / $b['due'] * 100, 1) : 0.0,
            ];
        }

        return $out;
    }

    /**
     * PURE. Projects a flat $avgDue (and $rate-weighted expected collection) across
     * $months calendar months starting at $now (inclusive).
     *
     * @return array<int, array{month:string, due:float, expected:float}>
     */
    public function buildForecast(float $avgDue, float $rate, int $months, ?Carbon $now = null): array
    {
        $now = ($now ?: Carbon::now())->copy()->startOfMonth();
        $out = [];
        for ($i = 0; $i < $months; $i++) {
            $out[] = [
                'month' => $now->copy()->addMonths($i)->format('Y-m'),
                'due' => round($avgDue, 2),
                'expected' => round($avgDue * $rate, 2),
            ];
        }

        return $out;
    }

    /**
     * PURE. Deterministic Arabic anomaly notes over a collectionHistory()-shaped
     * array. Rules: a month's due far above the running average of prior months,
     * negative remaining (overpayment), a sudden drop in collection rate between
     * consecutive months, and months with zero collection despite a positive due.
     *
     * @param  array<int, array{month:string, due:float, paid:float, remaining:float, rate:float}>  $history
     * @return array<int, string>
     */
    public function detectAnomalies(array $history): array
    {
        $notes = [];
        if (empty($history)) {
            return $notes;
        }

        $dues = array_column($history, 'due');

        foreach ($history as $i => $h) {
            $month = $h['month'];
            $due = (float) $h['due'];
            $paid = (float) $h['paid'];
            $remaining = (float) $h['remaining'];
            $rate = (float) $h['rate'];

            if ($i > 0) {
                $priorDues = array_slice($dues, 0, $i);
                $runningAvg = array_sum($priorDues) / count($priorDues);
                if ($runningAvg > 0 && $due > $runningAvg * self::DUE_SPIKE_MULTIPLIER) {
                    $notes[] = "شذوذ: مستحقات شهر {$month} ({$due}) أعلى بكثير من متوسط الأشهر السابقة ({$runningAvg})";
                }
            }

            if ($remaining < 0) {
                $notes[] = "شذوذ: رصيد سالب في شهر {$month} — المتبقي ({$remaining}) أقل من صفر، ما يشير إلى دفع زائد عن المستحق";
            }

            if ($due > 0 && $paid == 0.0) {
                $notes[] = "شذوذ: لا يوجد أي تحصيل في شهر {$month} رغم وجود مستحقات بقيمة {$due}";
            }

            if ($i > 0) {
                $prevRate = (float) $history[$i - 1]['rate'];
                $prevMonth = $history[$i - 1]['month'];
                if (($prevRate - $rate) > self::RATE_DROP_THRESHOLD) {
                    $notes[] = "شذوذ: انخفاض حاد في نسبة التحصيل من {$prevRate}% في شهر {$prevMonth} إلى {$rate}% في شهر {$month}";
                }
            }
        }

        return $notes;
    }

    /** PURE — prompt shape for the collection-trend narrative. */
    public function narrativePrompt(array $history): string
    {
        return "أنت محلل مالي متخصص في متابعة مستحقات ومدفوعات العمال. لديك بيانات التحصيل الشهرية التالية فقط بصيغة JSON (كل عنصر: الشهر، المستحق، المدفوع، المتبقي، نسبة التحصيل %)، ولا يوجد لديك أي بيانات أخرى (لا تخترع أرقاماً ولا تفترض بيانات غير موجودة هنا):\n"
            .json_encode($history, JSON_UNESCAPED_UNICODE)."\n"
            .'اكتب تحليلاً سردياً بالعربية من جملتين إلى ثلاث جمل يصف اتجاه التحصيل (تحسّن/تراجع/استقرار) بأسلوب واضح لصاحب العمل. أجب بنص عادي فقط دون أي تنسيق JSON.';
    }

    // ---- DB-fetching wrapper ----

    /**
     * Rows from `financial` (one per worker/month record, not deleted) LEFT JOINed
     * to `financial_detail` and grouped so each `financial` record yields a single
     * row with its total paid amount. Filtered to the [$fromIndex, $toIndex]
     * (year*12+month) window before touching PHP.
     *
     * @return array<int, array{y:int, m:int, val:float, paid:float}>
     */
    private function fetchFinancialRows(int $fromIndex, int $toIndex): array
    {
        return DB::table('financial as f')
            ->leftJoin('financial_detail as fd', 'fd.financial_id', '=', 'f.financial_id')
            ->select('f.financial_month_y as y', 'f.financial_month_m as m', 'f.financial_month_val as val')
            ->selectRaw('COALESCE(SUM(fd.financial_month_pay), 0) as paid')
            ->where('f.is_deleted', 0)
            ->whereRaw('(f.financial_month_y * 12 + f.financial_month_m) BETWEEN ? AND ?', [$fromIndex, $toIndex])
            ->groupBy('f.financial_id', 'f.financial_month_y', 'f.financial_month_m', 'f.financial_month_val')
            ->get()
            ->map(fn ($r) => ['y' => (int) $r->y, 'm' => (int) $r->m, 'val' => (float) $r->val, 'paid' => (float) $r->paid])
            ->all();
    }

}
