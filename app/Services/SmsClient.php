<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Spec 001 FR-007: provider-agnostic SMS sender for the multi-channel alert
 * layer (Rentals module).
 *
 * Configuration is read directly from env() (see .env keys documented in the
 * AlertDispatcher/SmsClient PR notes):
 *   SMS_PROVIDER  - driver key, e.g. "taqnyat"
 *   SMS_API_KEY   - bearer token / API key for the provider
 *   SMS_SENDER    - sender name/id registered with the provider
 *   SMS_BASE_URL  - override for the provider's API base URL (optional)
 *
 * If provider or API key are missing, send() no-ops and returns false
 * (logs a notice instead of throwing) so callers never have to special-case
 * "SMS not configured yet".
 *
 * To add a new provider: add a new case to the match() in send() and a
 * corresponding protected sendViaXxx() method.
 */
class SmsClient
{
    protected ?string $provider;

    protected ?string $apiKey;

    protected ?string $sender;

    protected ?string $baseUrl;

    public function __construct()
    {
        $this->provider = env('SMS_PROVIDER');
        $this->apiKey = env('SMS_API_KEY');
        $this->sender = env('SMS_SENDER');
        $this->baseUrl = env('SMS_BASE_URL');
    }

    public function send(string $phone, string $text): bool
    {
        if (empty($this->provider) || empty($this->apiKey)) {
            Log::notice('SmsClient: SMS not sent, provider or API key is not configured.', [
                'phone' => $phone,
            ]);

            return false;
        }

        return match (strtolower($this->provider)) {
            'taqnyat' => $this->sendViaTaqnyat($phone, $text),
            default => $this->sendUnsupportedProvider($phone),
        };
    }

    protected function sendUnsupportedProvider(string $phone): bool
    {
        Log::notice("SmsClient: unsupported SMS_PROVIDER [{$this->provider}], SMS not sent.", [
            'phone' => $phone,
        ]);

        return false;
    }

    /**
     * Taqnyat-style HTTP POST JSON with bearer token.
     * https://taqnyat.sa/ (Saudi SMS provider).
     */
    protected function sendViaTaqnyat(string $phone, string $text): bool
    {
        $url = $this->baseUrl ?: 'https://api.taqnyat.sa/v1/messages';

        try {
            $response = Http::withToken($this->apiKey)
                ->asJson()
                ->post($url, [
                    'recipients' => [$phone],
                    'body' => $text,
                    'sender' => $this->sender,
                ]);

            if (! $response->successful()) {
                Log::warning('SmsClient: Taqnyat responded with a non-success status.', [
                    'phone' => $phone,
                    'status' => $response->status(),
                ]);
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('SmsClient: Taqnyat send failed - '.$e->getMessage(), [
                'phone' => $phone,
            ]);

            return false;
        }
    }
}
