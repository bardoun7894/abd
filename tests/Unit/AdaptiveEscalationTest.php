<?php

// Adaptive escalation: weak first-pass results (low mean field_confidence) are
// re-read once on the stronger escalation model; confident scans make ONE call.
uses(Tests\TestCase::class);

use App\Services\GeminiClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.cache_enabled', false); // isolate from the result cache
    config()->set('services.gemini.default_model', 'gemini-flash-lite-latest');
    config()->set('services.gemini.escalation_model', 'gemini-3.5-flash');
    config()->set('services.gemini.escalation_thinking', 'medium');
    config()->set('services.gemini.escalation_confidence_floor', 0.5);
});

function adaptiveTmpFile(): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'aiesc').'.png';
    file_put_contents($tmp, 'PNGDATA');

    return $tmp;
}

function geminiJsonResponse(array $payload, int $in = 100, int $out = 20): array
{
    return [
        'candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
        'usageMetadata' => ['promptTokenCount' => $in, 'candidatesTokenCount' => $out],
    ];
}

it('escalates to the stronger model when first-pass confidence is low', function () {
    $requestedModels = [];
    Http::fake(function ($request) use (&$requestedModels) {
        // URL: {base}/models/{model}:generateContent?key=...
        if (preg_match('#/models/([^:]+):generateContent#', $request->url(), $m)) {
            $requestedModels[] = $m[1];
        }
        $confident = count($requestedModels) > 1; // second call = strong model

        return Http::response(geminiJsonResponse([
            'document_number' => $confident ? '12345' : null,
            'field_confidence' => ['document_number' => $confident ? 0.95 : 0.1],
        ]), 200);
    });

    $tmp = adaptiveTmpFile();
    $client = new GeminiClient();
    $schema = ['type' => 'OBJECT', 'properties' => ['document_number' => ['type' => 'STRING', 'nullable' => true]]];

    $result = $client->extractAdaptive('prompt', $tmp, $schema);
    @unlink($tmp);

    expect($requestedModels)->toBe(['gemini-flash-lite-latest', 'gemini-3.5-flash']);
    expect($result['document_number'])->toBe('12345');
    expect($client->lastEscalated)->toBeTrue();
    expect($client->lastModel)->toBe('gemini-3.5-flash');
});

it('makes a single cheap call when the first pass is confident', function () {
    $calls = 0;
    Http::fake(function () use (&$calls) {
        $calls++;

        return Http::response(geminiJsonResponse([
            'document_number' => '999',
            'field_confidence' => ['document_number' => 1.0],
        ]), 200);
    });

    $tmp = adaptiveTmpFile();
    $client = new GeminiClient();
    $schema = ['type' => 'OBJECT', 'properties' => ['document_number' => ['type' => 'STRING', 'nullable' => true]]];

    $result = $client->extractAdaptive('prompt', $tmp, $schema);
    @unlink($tmp);

    expect($calls)->toBe(1);
    expect($result['document_number'])->toBe('999');
    expect($client->lastEscalated)->toBeFalse();
    expect($client->lastModel)->toBe('gemini-flash-lite-latest');
});

it('keeps the first-pass result when the escalation call fails', function () {
    $calls = 0;
    Http::fake(function () use (&$calls) {
        $calls++;
        if ($calls === 1) {
            return Http::response(geminiJsonResponse([
                'document_number' => 'partial',
                'field_confidence' => ['document_number' => 0.2],
            ]), 200);
        }

        return Http::response(['error' => ['code' => 503]], 503);
    });

    $tmp = adaptiveTmpFile();
    $client = new GeminiClient();
    $schema = ['type' => 'OBJECT', 'properties' => ['document_number' => ['type' => 'STRING', 'nullable' => true]]];

    $result = $client->extractAdaptive('prompt', $tmp, $schema, null, 5, 1);
    @unlink($tmp);

    expect($result['document_number'])->toBe('partial');
    expect($client->lastEscalated)->toBeFalse();
    expect($client->lastModel)->toBe('gemini-flash-lite-latest');
});
