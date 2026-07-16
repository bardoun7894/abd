<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Spec 006 T6-3 — rentals analytics: "التوقعات المستقبلية للإيرادات" (future revenue
 * forecast) + "تحليل اتجاهات التحصيل باستخدام الذكاء الاصطناعي" (AI collection-trend
 * analysis), built on top of the existing `lease_payments` schedule.
 *
 * SAFETY MODEL (same as ReportsNlService): every number is computed here in PHP via
 * DB::table aggregates over a fixed table/column set — the model NEVER sees raw rows
 * and NEVER generates SQL. Gemini only receives the already-computed monthly
 * due/paid/rate numbers (as JSON text) and is asked to phrase an Arabic narrative
 * around them.
 *
 * The bucketing/weighting math (buildProjection/buildHistory/historicalCollectionRate)
 * is PURE — no DB, no container — so it's unit-testable in isolation. The public
 * projectRevenue()/collectionHistory() methods are thin DB-fetching wrappers around it,
 * mirroring LeaseScheduleGenerator's pure-core / DB-wrapper split.
 */
class LeaseForecastService
{
    /**
     * Next-N-months scheduled revenue (from lease_payments.due_date/amount), weighted by
     * the historical collection rate (paid ÷ due) over the last 6 months.
     *
     * @return array<int, array{month:string, scheduled:float, projected:float}>
     */
    public function projectRevenue(int $months = 6): array
    {
        $now = Carbon::now()->startOfMonth();

        $rate = $this->historicalCollectionRate($this->fetchHistoryRows(6, $now), $now);
        $scheduled = $this->fetchUpcomingRows($months, $now);

        return $this->buildProjection($scheduled, $rate, $months, $now);
    }

    /**
     * Paid vs due per past calendar month, for the last N months.
     *
     * @return array<int, array{month:string, due:float, paid:float, rate:float}>
     */
    public function collectionHistory(int $months = 6): array
    {
        $now = Carbon::now()->startOfMonth();
        $rows = $this->fetchHistoryRows($months, $now);

        return $this->buildHistory($rows, $months, $now);
    }

    /**
     * Short Arabic narrative of the collection trend, phrased by Gemini around the
     * pre-computed collectionHistory() numbers. Falls back to the raw numbers (no
     * narrative) if Gemini is unavailable or fails — never blocks the analytics page.
     *
     * @param  array|null  $history  inject pre-computed history (tests); defaults to collectionHistory(6)
     * @return array{narrative:?string, history:array, source:string, error?:string}
     */
    public function trendNarrative(?string $model = null, ?array $history = null): array
    {
        $history = $history ?? $this->collectionHistory(6);

        try {
            $text = $this->callGeminiText($this->trendPrompt($history), $model);

            return ['narrative' => trim($text), 'history' => $history, 'source' => 'ai'];
        } catch (\Throwable $e) {
            return ['narrative' => null, 'history' => $history, 'source' => 'fallback', 'error' => $e->getMessage()];
        }
    }

    // ---- pure core (unit tested directly, no DB) ----

    /**
     * PURE. Paid ÷ due ratio across the given rows. Defaults to 1.0 (best-guess full
     * collection) when there is no history at all, so an empty history never zeroes out
     * the forecast.
     *
     * @param  iterable<array{amount:float,status:string,paid_amount:?float}>  $rows
     */
    public function historicalCollectionRate(iterable $rows, ?Carbon $now = null): float
    {
        $due = 0.0;
        $paid = 0.0;
        foreach ($rows as $r) {
            $amount = (float) ($r['amount'] ?? 0);
            $due += $amount;
            if (($r['status'] ?? null) === 'paid') {
                $paidAmount = $r['paid_amount'] ?? null;
                $paid += is_numeric($paidAmount) ? (float) $paidAmount : $amount;
            }
        }

        return $due > 0 ? round($paid / $due, 4) : 1.0;
    }

    /**
     * PURE. Buckets scheduled (due) amounts into $months calendar months starting at
     * $now (inclusive), and derives a projected figure weighted by $rate.
     *
     * @param  iterable<array{due_date:string,amount:float}>  $rows
     * @return array<int, array{month:string, scheduled:float, projected:float}>
     */
    public function buildProjection(iterable $rows, float $rate, int $months, ?Carbon $now = null): array
    {
        $now = ($now ?: Carbon::now())->copy()->startOfMonth();
        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $buckets[$now->copy()->addMonths($i)->format('Y-m')] = 0.0;
        }

        foreach ($rows as $r) {
            $key = Carbon::parse($r['due_date'])->format('Y-m');
            if (array_key_exists($key, $buckets)) {
                $buckets[$key] += (float) ($r['amount'] ?? 0);
            }
        }

        $out = [];
        foreach ($buckets as $month => $scheduled) {
            $out[] = [
                'month' => $month,
                'scheduled' => round($scheduled, 2),
                'projected' => round($scheduled * $rate, 2),
            ];
        }

        return $out;
    }

    /**
     * PURE. Buckets due vs paid into $months calendar months ending at (and including)
     * the month before $now.
     *
     * @param  iterable<array{due_date:string,amount:float,status:string,paid_amount:?float}>  $rows
     * @return array<int, array{month:string, due:float, paid:float, rate:float}>
     */
    public function buildHistory(iterable $rows, int $months, ?Carbon $now = null): array
    {
        $now = ($now ?: Carbon::now())->copy()->startOfMonth();
        $buckets = [];
        for ($i = $months; $i >= 1; $i--) {
            $buckets[$now->copy()->subMonths($i)->format('Y-m')] = ['due' => 0.0, 'paid' => 0.0];
        }

        foreach ($rows as $r) {
            $key = Carbon::parse($r['due_date'])->format('Y-m');
            if (! array_key_exists($key, $buckets)) {
                continue;
            }
            $amount = (float) ($r['amount'] ?? 0);
            $buckets[$key]['due'] += $amount;
            if (($r['status'] ?? null) === 'paid') {
                $paidAmount = $r['paid_amount'] ?? null;
                $buckets[$key]['paid'] += is_numeric($paidAmount) ? (float) $paidAmount : $amount;
            }
        }

        $out = [];
        foreach ($buckets as $month => $b) {
            $out[] = [
                'month' => $month,
                'due' => round($b['due'], 2),
                'paid' => round($b['paid'], 2),
                'rate' => $b['due'] > 0 ? round($b['paid'] / $b['due'] * 100, 1) : 0.0,
            ];
        }

        return $out;
    }

    /** PURE — prompt shape for the collection-trend narrative. */
    public function trendPrompt(array $history): string
    {
        return "أنت محلل مالي متخصص في تحصيل إيجارات العقارات. لديك بيانات التحصيل الشهرية التالية فقط بصيغة JSON (كل عنصر: الشهر، المستحق، المحصّل، نسبة التحصيل %)، ولا يوجد لديك أي بيانات أخرى (لا تخترع أرقاماً ولا تفترض بيانات غير موجودة هنا):\n"
            .json_encode($history, JSON_UNESCAPED_UNICODE)."\n"
            .'اكتب تحليلاً سردياً بالعربية من جملتين إلى ثلاث جمل يصف اتجاه التحصيل (تحسّن/تراجع/استقرار) بأسلوب واضح لصاحب العمل. أجب بنص عادي فقط دون أي تنسيق JSON.';
    }

    // ---- DB-fetching wrappers ----

    /** Rows due in the $months calendar months before $now (for the historical rate / collectionHistory). */
    private function fetchHistoryRows(int $months, Carbon $now): array
    {
        $from = $now->copy()->subMonths($months);

        return DB::table('lease_payments')
            ->select('due_date', 'amount', 'status', 'paid_amount')
            ->whereDate('due_date', '>=', $from->format('Y-m-d'))
            ->whereDate('due_date', '<', $now->format('Y-m-d'))
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    /** Rows due in the $months calendar months starting at $now (for the projection). */
    private function fetchUpcomingRows(int $months, Carbon $now): array
    {
        $to = $now->copy()->addMonths($months);

        return DB::table('lease_payments')
            ->select('due_date', 'amount')
            ->whereDate('due_date', '>=', $now->format('Y-m-d'))
            ->whereDate('due_date', '<', $to->format('Y-m-d'))
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    /**
     * Minimal text-only Gemini call — copied verbatim from
     * MoraslatAiExtractor::callGeminiText() / ReportsNlService::callGeminiText()
     * (GeminiClient is file-input only and frozen).
     */
    private function callGeminiText(string $prompt, ?string $model = null): string
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        $model = $model ?: config('services.gemini.default_model');
        $base = rtrim(config('services.gemini.base_url'), '/');

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'text/plain',
            ],
        ];

        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        while (true) {
            $attempt++;
            $resp = Http::timeout((int) config('services.gemini.timeout', 120))->acceptJson()->post($url, $body);
            if ($resp->successful()) {
                break;
            }
            $status = $resp->status();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                usleep((int) ((2 ** $attempt) * 500_000));

                continue;
            }
            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');
        if ($text === null) {
            throw new RuntimeException('Gemini returned no content: '.$resp->body());
        }

        return $text;
    }
}
