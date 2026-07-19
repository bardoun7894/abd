<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// NOTE: AiSubscriptionGate lives in this same namespace (App\Services), so
// it's referenced below as app(AiSubscriptionGate::class) with no `use`
// needed. Resolved via the container at call time (not constructor DI)
// since every extractor in the app instantiates GeminiClient directly with
// `new GeminiClient()`.

/**
 * Reusable Google Gemini JSON-extraction call, shared by every module's AI extractor
 * (Spec 004). Sends one document (image/PDF) + a prompt and a responseSchema, returns
 * decoded JSON. Mirrors the hardened call in InvoiceExtractionService (retry/backoff,
 * temperature 0, forced JSON) but standalone so new modules reuse it without touching
 * the working invoice pipeline.
 */
class GeminiClient
{
    /** usageMetadata (token counts) from the most recent call. */
    public array $lastUsage = [];

    /** Model used by the most recent extract()/generateText() call. */
    public ?string $lastModel = null;

    /** True when extractAdaptive()'s escalation pass produced the returned result. */
    public bool $lastEscalated = false;

    public function lastInputTokens(): int
    {
        return (int) ($this->lastUsage['promptTokenCount'] ?? 0);
    }

    public function lastOutputTokens(): int
    {
        return (int) ($this->lastUsage['candidatesTokenCount'] ?? 0) + (int) ($this->lastUsage['thoughtsTokenCount'] ?? 0);
    }

    /**
     * Adaptive extraction: first pass on the cheap default model; if the mean
     * field_confidence is below the configured floor (hard/unclear scan), re-read
     * the SAME file once on the stronger escalation model with deeper thinking.
     * The escalated result wins only when its confidence is actually higher, and
     * any escalation failure silently keeps the first-pass result — escalation is
     * an upgrade, never a new way to break.
     *
     * @param  array  $schema  Gemini responseSchema (OBJECT)
     * @return array decoded JSON object
     *
     * @throws RuntimeException on config/HTTP/JSON failure of the FIRST pass
     */
    public function extractAdaptive(string $prompt, string $filePath, array $schema, ?string $model = null, ?int $timeout = null, ?int $maxAttempts = null): array
    {
        $this->lastEscalated = false;

        $raw = $this->extract($prompt, $filePath, $schema, $model, null, $timeout, $maxAttempts);
        $firstModel = $this->lastModel;
        $firstUsage = $this->lastUsage;

        if (! (bool) config('services.gemini.interactive_escalate', true)) {
            return $raw;
        }
        $strong = (string) config('services.gemini.escalation_model', 'gemini-3.5-flash');
        $floor = (float) config('services.gemini.escalation_confidence_floor', 0.5);
        if ($strong === $firstModel || $this->confidenceScore($raw) >= $floor) {
            return $raw; // clear scan — or already on the strong model
        }

        try {
            $better = $this->extract($prompt, $filePath, $schema, $strong, config('services.gemini.escalation_thinking', 'medium'), $timeout, $maxAttempts);
            if ($this->confidenceScore($better) > $this->confidenceScore($raw)) {
                $this->lastEscalated = true;
                Log::info('AI escalation improved extraction confidence', ['from' => $firstModel, 'to' => $strong]);

                return $better;
            }
        } catch (\Throwable $e) {
            Log::warning('AI escalation failed; keeping first-pass result', ['model' => $strong, 'error' => $e->getMessage()]);
        }

        // Escalated pass didn't win (or failed) — restore first-pass bookkeeping
        // so callers report the model/tokens of the result they actually got.
        $this->lastModel = $firstModel;
        $this->lastUsage = $firstUsage;

        return $raw;
    }

    /**
     * 0..1 quality score for a decoded extraction: mean of field_confidence when
     * present, otherwise the share of filled non-meta fields.
     */
    private function confidenceScore(array $raw): float
    {
        $fc = $raw['field_confidence'] ?? null;
        if (is_array($fc) && $fc !== []) {
            $sum = 0.0;
            $n = 0;
            foreach ($fc as $v) {
                if (is_numeric($v)) {
                    $sum += (float) $v;
                    $n++;
                }
            }

            return $n ? $sum / $n : 0.0;
        }
        $filled = 0;
        $total = 0;
        foreach ($raw as $k => $v) {
            if (str_starts_with((string) $k, '_')) {
                continue;
            }
            $total++;
            if ($v !== null && $v !== '') {
                $filled++;
            }
        }

        return $total ? $filled / $total : 0.0;
    }

    /**
     * Generate plain text from a prompt via Gemini (text-only call).
     *
     * Mirrors extract()'s retry/backoff/error handling, but sends a single text
     * part with responseMimeType text/plain and returns the raw generated string.
     *
     * @throws RuntimeException on config/HTTP/missing-content failure
     */
    public function generateText(string $prompt, ?string $model = null): string
    {
        // Spec 007 — central AI-subscription gate. Throws (Arabic message)
        // when the subscription is inactive, expired, or quota-exhausted.
        app(AiSubscriptionGate::class)->assertAllowed();

        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        $model = $model ?: config('services.gemini.default_model');
        $this->lastModel = $model;
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
        $resp = null;
        $lastStatus = null;
        $lastBody = null;
        while (true) {
            $attempt++;
            try {
                $resp = Http::timeout((int) config('services.gemini.timeout', 120))->acceptJson()->post($url, $body);
            } catch (ConnectionException $e) {
                $lastStatus = 'connection';
                $lastBody = $e->getMessage();
                if ($attempt < $maxAttempts) {
                    Log::warning('Gemini transient HTTP error; retrying', [
                        'model' => $model,
                        'status' => 'connection',
                        'attempt' => $attempt,
                        'max_attempts' => $maxAttempts,
                    ]);
                    usleep((int) ((2 ** $attempt) * 500_000));

                    continue;
                }
                Log::error('Gemini HTTP request failed after retries', [
                    'model' => $model,
                    'status' => 'connection',
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'response' => substr($lastBody, 0, 1000),
                ]);
                throw new RuntimeException('Gemini connection failed: '.$e->getMessage(), 0, $e);
            }

            if ($resp->successful()) {
                break;
            }
            $status = $resp->status();
            $lastStatus = $status;
            $lastBody = $resp->body();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                Log::warning('Gemini transient HTTP error; retrying', [
                    'model' => $model,
                    'status' => $status,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                ]);
                usleep((int) ((2 ** $attempt) * 500_000));

                continue;
            }
            Log::error('Gemini HTTP request failed after retries', [
                'model' => $model,
                'status' => $status,
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'response' => substr($lastBody, 0, 1000),
            ]);
            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $this->lastUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        Log::info('Gemini text generation completed', [
            'model' => $model,
            'attempts' => $attempt,
            'input_tokens' => $this->lastInputTokens(),
            'output_tokens' => $this->lastOutputTokens(),
        ]);

        return $this->extractTextResponse($resp->json());
    }

    /**
     * Robustly pull the plain-text payload out of a Gemini generateContent response.
     * Thinking models may return thought parts first; skip them and return the first
     * real text part. Throws if no text content is present.
     */
    protected function extractTextResponse($json): string
    {
        $parts = (array) data_get($json, 'candidates.0.content.parts', []);
        foreach ($parts as $part) {
            if (! empty($part['thought'])) {
                continue;
            }
            if (isset($part['text']) && is_string($part['text'])) {
                return $part['text'];
            }
        }

        $fallback = data_get($json, 'candidates.0.content.parts.0.text');
        if (is_string($fallback)) {
            return $fallback;
        }

        throw new RuntimeException('Gemini returned no content: '.json_encode($json));
    }

    /**
     * Extract structured JSON from a file via Gemini.
     *
     * @param  array  $schema  Gemini responseSchema (OBJECT)
     * @return array decoded JSON object
     *
     * @throws RuntimeException on config/HTTP/JSON failure
     */
    public function extract(string $prompt, string $filePath, array $schema, ?string $model = null, ?string $thinking = null, ?int $timeout = null, ?int $maxAttempts = null): array
    {
        // Spec 007 — central AI-subscription gate. Throws (Arabic message)
        // when the subscription is inactive, expired, or quota-exhausted.
        // This single choke point covers every caller (invoices, leases,
        // shop, worker/vehicle/expense/... extractors) since they all
        // instantiate GeminiClient directly and call extract().
        app(AiSubscriptionGate::class)->assertAllowed();

        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        if (! is_file($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }
        $model = $model ?: config('services.gemini.default_model');
        $this->lastModel = $model;
        $base = rtrim(config('services.gemini.base_url'), '/');

        $generationConfig = [
            'temperature' => 0,
            'responseMimeType' => 'application/json',
            'responseSchema' => $schema,
        ];
        $level = $thinking ?: config('services.gemini.thinking_level');
        if ($level && str_contains($model, 'gemini-3')) {
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => $level];
        }

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => ['mime_type' => $this->mimeFor($filePath), 'data' => base64_encode(file_get_contents($filePath))]],
                ],
            ]],
            'generationConfig' => $generationConfig,
        ];

        $url = "{$base}/models/{$model}:generateContent?key={$key}";
        // Interactive callers (shop/purchase/worker/... AJAX prefill) pass a short
        // timeout + few retries so a slow/overloaded model fails fast instead of
        // holding a PHP-FPM worker for minutes; background pipelines omit these and
        // keep the generous config defaults.
        $maxAttempts = $maxAttempts ?? (int) config('services.gemini.retries', 4);
        $httpTimeout = $timeout ?? (int) config('services.gemini.page_timeout', 120);

        // --- Unified result cache (dedup) + concurrency cap + usage ledger. Fail-open:
        // any cache/DB/lock hiccup must never block or break extraction. ---
        $cacheEnabled = (bool) config('services.gemini.cache_enabled', true);
        $module = $this->guessModule();
        // Thinking level is part of the key: an escalated deeper-thinking re-read
        // must not be served the shallow first pass's cached result.
        $cacheKey = $cacheEnabled ? $this->extractionCacheKey($model.'@'.($level ?: 'default'), $prompt, $schema, $filePath) : null;
        if ($cacheKey !== null) {
            $cached = $this->readExtractionCache($cacheKey);
            if ($cached !== null) {
                $this->lastUsage = [
                    'promptTokenCount' => (int) ($cached->input_tokens ?? 0),
                    'candidatesTokenCount' => (int) ($cached->output_tokens ?? 0),
                ];
                $this->bumpExtractionCacheHit($cacheKey);
                $this->logAiUsage($module, $model, true, (int) $cached->input_tokens, (int) $cached->output_tokens, (float) $cached->est_cost_usd);
                $decodedCached = json_decode($cached->result_json, true);
                if (is_array($decodedCached)) {
                    return $decodedCached; // instant, zero-cost hit — no HTTP, no quota
                }
            }
        }

        // Concurrency cap: a burst of AI calls must not tie up every PHP-FPM worker.
        $slot = $this->acquireAiSlot();
        if ($slot === null) {
            throw new RuntimeException('النظام مشغول بمعالجة طلبات الذكاء الاصطناعي حالياً، يرجى المحاولة بعد قليل.');
        }

        $attempt = 0;
        $resp = null;
        $lastStatus = null;
        $lastBody = null;
        try {
        while (true) {
            $attempt++;
            try {
                $resp = Http::timeout($httpTimeout)->acceptJson()->post($url, $body);
            } catch (ConnectionException $e) {
                $lastStatus = 'connection';
                $lastBody = $e->getMessage();
                if ($attempt < $maxAttempts) {
                    Log::warning('Gemini transient HTTP error; retrying', [
                        'model' => $model,
                        'status' => 'connection',
                        'attempt' => $attempt,
                        'max_attempts' => $maxAttempts,
                    ]);
                    usleep((int) ((2 ** $attempt) * 500_000));

                    continue;
                }
                Log::error('Gemini HTTP request failed after retries', [
                    'model' => $model,
                    'status' => 'connection',
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'response' => substr($lastBody, 0, 1000),
                ]);
                throw new RuntimeException('Gemini connection failed: '.$e->getMessage(), 0, $e);
            }

            if ($resp->successful()) {
                break;
            }
            $status = $resp->status();
            $lastStatus = $status;
            $lastBody = $resp->body();
            if (in_array($status, [429, 500, 502, 503, 504], true) && $attempt < $maxAttempts) {
                Log::warning('Gemini transient HTTP error; retrying', [
                    'model' => $model,
                    'status' => $status,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                ]);
                usleep((int) ((2 ** $attempt) * 500_000));

                continue;
            }
            Log::error('Gemini HTTP request failed after retries', [
                'model' => $model,
                'status' => $status,
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'response' => substr($lastBody, 0, 1000),
            ]);
            throw new RuntimeException('Gemini HTTP '.$status.': '.$resp->body(), $status);
        }

        $this->lastUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        Log::info('Gemini extraction completed', [
            'model' => $model,
            'attempts' => $attempt,
            'input_tokens' => $this->lastInputTokens(),
            'output_tokens' => $this->lastOutputTokens(),
        ]);

        $decoded = $this->decodeJsonResponse($resp->json());

        // Spec 007 — count usage against the quota only once extraction of
        // this file actually produced usable JSON (a thrown/failed call
        // below never reaches here, so it never consumes quota). extract()
        // is called once per page/file across every extractor in the app,
        // so "1 call = 1 page" here is the least-invasive, single-choke-point
        // place to meter pages without touching each module's pipeline.
        app(AiSubscriptionGate::class)->recordPages(1);

        // Cache the result (dedup future identical calls) + log the usage (miss).
        if ($cacheKey !== null) {
            $this->writeExtractionCache($cacheKey, $module, $model, $filePath, $decoded, $this->lastInputTokens(), $this->lastOutputTokens());
        }
        $this->logAiUsage($module, $model, false, $this->lastInputTokens(), $this->lastOutputTokens(), $this->estCostUsd($this->lastInputTokens(), $this->lastOutputTokens(), $model));

        return $decoded;
        } finally {
            $this->releaseAiSlot($slot);
        }
    }

    /**
     * Robustly pull the JSON payload out of a Gemini generateContent response.
     * Thinking models return multiple parts (thought parts first) and can also
     * truncate into degenerate output. Strategy: scan all parts, skip thought
     * parts, strip markdown fences, then balanced-brace salvage.
     */
    protected function decodeJsonResponse($json): array
    {
        $parts = (array) data_get($json, 'candidates.0.content.parts', []);
        $texts = [];
        foreach ($parts as $part) {
            if (! empty($part['thought'])) {
                continue;
            }
            if (isset($part['text']) && is_string($part['text'])) {
                $texts[] = $part['text'];
            }
        }
        if ($texts === []) {
            $fallback = data_get($json, 'candidates.0.content.parts.0.text');
            if (is_string($fallback)) {
                $texts[] = $fallback;
            }
        }
        if ($texts === []) {
            throw new RuntimeException('Gemini returned no content: '.json_encode($json));
        }

        foreach ($texts as $text) {
            $decoded = $this->tryDecodeJson($text);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        throw new RuntimeException('Gemini returned non-JSON: '.substr($texts[0], 0, 2000));
    }

    /** Decode one text part: direct, fence-stripped, then balanced-brace salvage. */
    private function tryDecodeJson(string $text): ?array
    {
        $text = trim($text);
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $text, $m)) {
            $text = trim($m[1]);
        }
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }
        $depth = 0;
        $inStr = false;
        $esc = false;
        $len = strlen($text);
        $lastCommaAtDepth1 = null;
        for ($i = $start; $i < $len; $i++) {
            $c = $text[$i];
            if ($inStr) {
                if ($esc) { $esc = false; }
                elseif ($c === '\\') { $esc = true; }
                elseif ($c === '"') { $inStr = false; }
                continue;
            }
            if ($c === '"') { $inStr = true; }
            elseif ($c === '{') { $depth++; }
            elseif ($c === ',') {
                if ($depth === 1) { $lastCommaAtDepth1 = $i; }
            }
            elseif ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    $candidate = substr($text, $start, $i - $start + 1);
                    $decoded = json_decode($candidate, true);
                    return is_array($decoded) ? $decoded : null;
                }
            }
        }

        // Truncated mid-string / mid-object (MAX_TOKENS): keep every complete
        // top-level pair, drop the unfinished tail, close the object.
        if ($lastCommaAtDepth1 !== null) {
            $candidate = substr($text, $start, $lastCommaAtDepth1 - $start).'}';
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    public function mimeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    // ---- Unified cache + concurrency + usage helpers (all fail-open) ----

    /** Best-effort module label (calling extractor/service class basename) for the ledger. */
    private function guessModule(): ?string
    {
        try {
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
                $cls = $frame['class'] ?? '';
                if ($cls && $cls !== static::class && (str_contains($cls, 'Extractor') || str_contains($cls, 'Service') || str_contains($cls, 'Pipeline'))) {
                    return class_basename($cls);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    /** Deterministic cache key: model + prompt + schema + file content hash. */
    private function extractionCacheKey(string $model, string $prompt, array $schema, string $filePath): ?string
    {
        try {
            $fileHash = @hash_file('sha256', $filePath);
            if ($fileHash === false) {
                return null;
            }

            return hash('sha256', $model.'|'.$prompt.'|'.json_encode($schema).'|'.$fileHash);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function readExtractionCache(string $key): ?object
    {
        try {
            $ttlDays = (int) config('services.gemini.cache_ttl_days', 90);

            return DB::table('ai_extractions')
                ->where('cache_key', $key)
                ->when($ttlDays > 0, fn ($q) => $q->where('created_at', '>=', now()->subDays($ttlDays)))
                ->first();
        } catch (\Throwable $e) {
            return null; // table missing / DB down → treat as miss
        }
    }

    private function bumpExtractionCacheHit(string $key): void
    {
        try {
            DB::table('ai_extractions')->where('cache_key', $key)->increment('hit_count');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function writeExtractionCache(string $key, ?string $module, ?string $model, string $filePath, array $result, int $in, int $out): void
    {
        try {
            DB::table('ai_extractions')->updateOrInsert(
                ['cache_key' => $key],
                [
                    'module' => $module,
                    'model' => $model,
                    'file_hash' => @hash_file('sha256', $filePath) ?: null,
                    'result_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'input_tokens' => $in,
                    'output_tokens' => $out,
                    'est_cost_usd' => $this->estCostUsd($in, $out, $model),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            // ignore — caching is best-effort
        }
    }

    private function logAiUsage(?string $module, ?string $model, bool $hit, int $in, int $out, float $cost): void
    {
        try {
            DB::table('ai_usage_log')->insert([
                'module' => $module,
                'model' => $model,
                'cache_hit' => $hit,
                'input_tokens' => $in,
                'output_tokens' => $out,
                'est_cost_usd' => $cost,
                'user_id' => Auth::check() ? Auth::id() : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore — the ledger is best-effort
        }
    }

    private function estCostUsd(int $in, int $out, ?string $model = null): float
    {
        $prices = (array) config('services.gemini.model_prices', []);
        if ($model !== null && isset($prices[$model])) {
            [$pin, $pout] = $prices[$model];
        } else {
            $pin = (float) config('services.gemini.price_in_per_m', 0);
            $pout = (float) config('services.gemini.price_out_per_m', 0);
        }

        return round($in / 1_000_000 * (float) $pin + $out / 1_000_000 * (float) $pout, 6);
    }

    /**
     * Acquire one of N concurrency slots via an atomic Cache::add semaphore.
     * Returns the slot key on success, or null when all slots are taken (caller
     * fast-fails). Slots auto-expire (slot_ttl) so a crashed request never leaks one.
     * Fail-open: if the cache store errors, returns a sentinel so extraction proceeds.
     */
    private function acquireAiSlot(): ?string
    {
        $max = (int) config('services.gemini.max_concurrent', 3);
        if ($max <= 0) {
            return 'ai_slot_disabled';
        }
        $ttl = (int) config('services.gemini.slot_ttl', 90);
        try {
            for ($i = 0; $i < $max; $i++) {
                $key = 'ai_slot_'.$i;
                if (Cache::add($key, 1, $ttl)) {
                    return $key;
                }
            }

            return null; // all busy
        } catch (\Throwable $e) {
            return 'ai_slot_bypass'; // cache store issue → don't block extraction
        }
    }

    private function releaseAiSlot(?string $slot): void
    {
        if ($slot === null || $slot === 'ai_slot_disabled' || $slot === 'ai_slot_bypass') {
            return;
        }
        try {
            Cache::forget($slot);
        } catch (\Throwable $e) {
            // ignore — the slot's TTL will release it
        }
    }
}
