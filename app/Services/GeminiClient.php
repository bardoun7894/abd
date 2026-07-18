<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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

    public function lastInputTokens(): int
    {
        return (int) ($this->lastUsage['promptTokenCount'] ?? 0);
    }

    public function lastOutputTokens(): int
    {
        return (int) ($this->lastUsage['candidatesTokenCount'] ?? 0) + (int) ($this->lastUsage['thoughtsTokenCount'] ?? 0);
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
    public function extract(string $prompt, string $filePath, array $schema, ?string $model = null, ?string $thinking = null): array
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
        if (! is_file($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }
        $model = $model ?: config('services.gemini.default_model');
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
        $maxAttempts = (int) config('services.gemini.retries', 4);
        $attempt = 0;
        $resp = null;
        $lastStatus = null;
        $lastBody = null;
        while (true) {
            $attempt++;
            try {
                $resp = Http::timeout((int) config('services.gemini.page_timeout', 120))->acceptJson()->post($url, $body);
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

        return $this->decodeJsonResponse($resp->json());
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
}
