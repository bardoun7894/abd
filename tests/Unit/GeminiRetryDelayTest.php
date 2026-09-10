<?php

// Client 2026-09-10 «حتى بعد الدفع ما زالت المشكلة». The 429s are real, but the
// retry made them fatal: Gemini puts its wait in error.details[] as a RetryInfo
// block, and retryAfterSeconds() only looked at the top level and error.*, so it
// returned 0 and the backoff fell to its 1s/2s/4s floor. A limit that asks for
// 11s therefore burned all 4 attempts inside its own window and the page failed.
uses(Tests\TestCase::class);

use App\Services\GeminiClient;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;

/**
 * Call the private retryAfterSeconds() with a real client Response built by hand.
 * Not Http::fake() — its stubs accumulate, so a second fake in the same test never
 * matches and every call would see the first body.
 */
function retryAfterVia(array $body, array $headers = []): int
{
    $resp = new Response(new Psr7Response(429, $headers + ['Content-Type' => 'application/json'], json_encode($body)));

    $ref = new ReflectionMethod(GeminiClient::class, 'retryAfterSeconds');
    $ref->setAccessible(true);

    return $ref->invoke(new GeminiClient(), $resp);
}

/** The exact body Gemini returned on noor-alsabah.com, 2026-09-10 07:39. */
function realQuotaBody(string $delay = '11s'): array
{
    return ['error' => [
        'code' => 429,
        'message' => 'You exceeded your current quota... Please retry in 11.33793498s.',
        'status' => 'RESOURCE_EXHAUSTED',
        'details' => [
            ['@type' => 'type.googleapis.com/google.rpc.Help', 'links' => [['url' => 'https://ai.google.dev/gemini-api/docs/rate-limits']]],
            ['@type' => 'type.googleapis.com/google.rpc.QuotaFailure', 'violations' => [[
                'quotaMetric' => 'generativelanguage.googleapis.com/generate_content_free_tier_requests',
                'quotaId' => 'GenerateRequestsPerMinutePerProjectPerModel-FreeTier',
                'quotaValue' => '15',
            ]]],
            ['@type' => 'type.googleapis.com/google.rpc.RetryInfo', 'retryDelay' => $delay],
        ],
    ]];
}

it('reads retryDelay out of the RetryInfo block in error.details', function () {
    expect(retryAfterVia(realQuotaBody('11s')))->toBe(11);
    expect(retryAfterVia(realQuotaBody('30s')))->toBe(30);
});

it('rounds a fractional retryDelay up so we never wake a moment too early', function () {
    expect(retryAfterVia(realQuotaBody('11.33793498s')))->toBe(12);
    expect(retryAfterVia(realQuotaBody('0.4s')))->toBe(1);
});

it('falls back to the number of seconds named in the message when there is no RetryInfo', function () {
    $b = realQuotaBody();
    unset($b['error']['details'][2]);
    $b['error']['details'] = array_values($b['error']['details']);

    expect(retryAfterVia($b))->toBe(12); // "Please retry in 11.33793498s."
});

it('still honours the Retry-After header and the older shapes', function () {
    expect(retryAfterVia(['error' => []], ['Retry-After' => '7']))->toBe(7);
    expect(retryAfterVia(['retryDelay' => '5s']))->toBe(5);
    expect(retryAfterVia(['error' => ['retryDelay' => 9]]))->toBe(9);
});

it('returns 0 when the response says nothing about waiting', function () {
    expect(retryAfterVia(['error' => ['code' => 429, 'message' => 'nope']]))->toBe(0);
});

it('caps an absurd retryDelay so one bad response cannot stall the queue worker', function () {
    expect(retryAfterVia(realQuotaBody('86400s')))->toBe(120);
});
