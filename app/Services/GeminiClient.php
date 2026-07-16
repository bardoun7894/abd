<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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

        $this->lastUsage = (array) data_get($resp->json(), 'usageMetadata', []);
        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');
        if ($text === null) {
            throw new RuntimeException('Gemini returned no content: '.$resp->body());
        }
        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini returned non-JSON: '.$text);
        }

        return $decoded;
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
