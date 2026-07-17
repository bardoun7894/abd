<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Batch-level AI summary ("🧠 ملخص الذكاء الاصطناعي للدفعة"). All numbers are
 * computed here in PHP (Eloquent over the isolated `invoices` connection) —
 * Gemini only phrases a short Arabic narrative around the already-computed
 * aggregates and never invents, joins, or derives its own figures. Same
 * safety model as ReportsNlService / HomeInsightService.
 *
 * Text-only Gemini call — GeminiClient::extract() requires an inline file part and is
 * frozen/shared, so callGeminiText() below is copied verbatim from
 * MoraslatAiExtractor::callGeminiText() (same retry/backoff/error handling).
 */
class InvoiceBatchSummarizer
{
    /**
     * DB aggregation (no network/LLM call) — reads the real Invoice/InvoiceBatch
     * rows for the batch and reduces them to the numbers Gemini is later allowed
     * to phrase.
     *
     * @return array{
     *   batch_id:int, invoice_count:int, supplier_count:int,
     *   total_incl_vat:float, vat_amount:float, needs_review_count:int,
     *   top_supplier:?array{name:string,count:int},
     *   top_suppliers_by_amount:array<int,array{name:string,count:int,total:float}>,
     *   model_used:?string
     * }
     */
    public function aggregates(int $batchId): array
    {
        $batch = InvoiceBatch::find($batchId);
        $invoices = Invoice::where('batch_id', $batchId)
            ->get(['supplier_name', 'total_incl_vat', 'vat_amount', 'needs_review']);

        $invoiceCount = $invoices->count();

        $suppliers = $invoices
            ->map(fn (Invoice $i) => trim((string) $i->supplier_name))
            ->filter(fn ($name) => $name !== '')
            ->unique();

        $totalInclVat = (float) $invoices->sum(fn (Invoice $i) => (float) ($i->total_incl_vat ?? 0));
        $vatAmount = (float) $invoices->sum(fn (Invoice $i) => (float) ($i->vat_amount ?? 0));
        $needsReviewCount = $invoices->filter(fn (Invoice $i) => (bool) $i->needs_review)->count();

        $bySupplier = $this->groupBySupplier($invoices);
        $topSupplier = $bySupplier->sortByDesc('count')->first();
        $topSuppliersByAmount = $bySupplier->sortByDesc('total')->take(3)->values()->all();

        return [
            'batch_id' => $batchId,
            'invoice_count' => $invoiceCount,
            'supplier_count' => $suppliers->count(),
            'total_incl_vat' => round($totalInclVat, 2),
            'vat_amount' => round($vatAmount, 2),
            'needs_review_count' => $needsReviewCount,
            'top_supplier' => $topSupplier ? ['name' => $topSupplier['name'], 'count' => $topSupplier['count']] : null,
            'top_suppliers_by_amount' => $topSuppliersByAmount,
            'model_used' => $batch?->model_used,
        ];
    }

    /** PURE — groups invoices by (trimmed, non-blank) supplier name with count + amount total. */
    private function groupBySupplier(Collection $invoices): Collection
    {
        return $invoices
            ->map(fn (Invoice $i) => [
                'name' => trim((string) $i->supplier_name),
                'amount' => (float) ($i->total_incl_vat ?? 0),
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->groupBy('name')
            ->map(fn (Collection $group, string $name) => [
                'name' => $name,
                'count' => $group->count(),
                'total' => round((float) $group->sum('amount'), 2),
            ])
            ->values();
    }

    /**
     * Gemini-phrased Arabic narrative around aggregates(). Cached per batch id +
     * processed_pages so it refreshes automatically as the batch fills in, and
     * gracefully falls back to narrative=null (never throws) if Gemini is down —
     * the caller must render the raw aggregate numbers in that case.
     *
     * @return array (aggregates() shape + 'narrative':?string)
     */
    public function summarize(int $batchId, ?string $model = null): array
    {
        $batch = InvoiceBatch::find($batchId);
        $aggregates = $this->aggregates($batchId);

        $narrative = null;
        try {
            $key = $this->cacheKey($batchId, $batch?->processed_pages);
            $narrative = Cache::remember($key, now()->addHours(6), function () use ($aggregates, $model) {
                return trim($this->callGeminiText($this->narratePrompt($aggregates), $model));
            });
        } catch (Throwable $e) {
            // Graceful fallback — the results page must never break because Gemini is down.
            $narrative = null;
        }

        return array_merge($aggregates, ['narrative' => $narrative]);
    }

    /** PURE — cache key keyed by batch id + processed_pages so it refreshes as the batch changes. */
    public function cacheKey(int $batchId, ?int $processedPages = null): string
    {
        return 'invoice_batch_ai_summary_'.$batchId.'_'.($processedPages ?? 0);
    }

    /** PURE — prompt shape. Only the pre-computed aggregates are sent, never raw rows. */
    public function narratePrompt(array $aggregates): string
    {
        return "أنت محلل مالي مختصر. لديك الأرقام الإجمالية التالية لدفعة فواتير بصيغة JSON فقط، ولا يوجد لديك أي بيانات أخرى (لا تخترع أرقاماً ولا تفترض بيانات غير موجودة هنا):\n"
            .json_encode($aggregates, JSON_UNESCAPED_UNICODE)."\n"
            .'اكتب ملخصاً سردياً بالعربية من جملتين إلى ثلاث جمل يوضح هذه الأرقام بأسلوب واضح لصاحب العمل، مع الإشارة إلى أهم مورد وعدد الفواتير التي تحتاج مراجعة إن وُجدت. أجب بنص عادي فقط دون أي تنسيق JSON.';
    }

    /**
     * Minimal text-only Gemini call — copied verbatim from
     * MoraslatAiExtractor::callGeminiText() (GeminiClient is file-input only and frozen).
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
