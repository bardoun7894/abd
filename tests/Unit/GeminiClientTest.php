<?php

// Unit tests for the shared GeminiClient: covers text-only generation and the
// structured file extraction is already exercised by InvoiceGeminiTest / LeaseGeminiTest.
uses(Tests\TestCase::class);

use App\Services\GeminiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
});

it('returns the generated text from a successful Gemini response', function () {
    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => '  ملخص عربي.  ']]],
            ]],
            'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 4],
        ], 200),
    ]);

    $client = new GeminiClient();
    $text = $client->generateText('ألخّص');

    expect($text)->toBe('  ملخص عربي.  ');
    expect($client->lastInputTokens())->toBe(10);
    expect($client->lastOutputTokens())->toBe(4);
});

it('retries on 429 and eventually returns the generated text', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 3) {
            return Http::response(['error' => ['code' => 429]], 429);
        }

        return Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'نجاح بعد إعادة المحاولة']]],
            ]],
            'usageMetadata' => ['promptTokenCount' => 5, 'candidatesTokenCount' => 3],
        ], 200);
    });

    $client = new GeminiClient();
    $text = $client->generateText('أعد المحاولة');

    expect($text)->toBe('نجاح بعد إعادة المحاولة');
    expect($attempts)->toBe(3);
});

it('retries on 5xx and eventually returns the generated text', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            return Http::response(['error' => ['code' => 503]], 503);
        }

        return Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'تم']]],
            ]],
        ], 200);
    });

    $client = new GeminiClient();

    expect($client->generateText('اختبار'))->toBe('تم');
    expect($attempts)->toBe(2);
});

it('throws when Gemini keeps returning retryable errors beyond the retry limit', function () {
    config()->set('services.gemini.retries', 2);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    $client = new GeminiClient();

    expect(fn () => $client->generateText('فشل'))
        ->toThrow(RuntimeException::class, 'Gemini HTTP 500');
});

it('throws when the Gemini response contains no text content', function () {
    Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => []]]]], 200)]);

    $client = new GeminiClient();

    expect(fn () => $client->generateText('لا محتوى'))
        ->toThrow(RuntimeException::class, 'Gemini returned no content');
});

it('skips thought parts and returns the real generated text', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [[
            'content' => ['parts' => [
                ['thought' => 'this is internal reasoning'],
                ['text' => 'النص الفعلي'],
            ]],
        ]],
    ], 200)]);

    $client = new GeminiClient();

    expect($client->generateText('مع أفكار'))->toBe('النص الفعلي');
});

it('uses the provided model override', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [[
            'content' => ['parts' => [['text' => 'ok']]],
        ]],
    ], 200)]);

    $client = new GeminiClient();
    $client->generateText('نموذج مخصص', 'gemini-custom');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'models/gemini-custom'));
});

it('sends a text/plain generation config for text-only calls', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [[
            'content' => ['parts' => [['text' => 'ok']]],
        ]],
    ], 200)]);

    $client = new GeminiClient();
    $client->generateText('نص فقط');

    Http::assertSent(
        fn ($req) => data_get($req->data(), 'generationConfig.responseMimeType') === 'text/plain'
            && data_get($req->data(), 'contents.0.parts.0.text') === 'نص فقط'
            && ! collect(data_get($req->data(), 'contents.0.parts'))->contains(fn ($p) => isset($p['inline_data']))
    );
});

it('retries on connection timeout and eventually returns the generated text', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 3) {
            throw new ConnectionException('cURL error 28: Connection timed out');
        }

        return Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'connected']]],
            ]],
        ], 200);
    });

    $client = new GeminiClient();

    expect($client->generateText('timeout test'))->toBe('connected');
    expect($attempts)->toBe(3);
});

it('throws when connection timeouts exceed the retry limit', function () {
    config()->set('services.gemini.retries', 2);
    Http::fake(function () {
        throw new ConnectionException('cURL error 56: Recv failure');
    });

    $client = new GeminiClient();

    expect(fn () => $client->generateText('fail'))
        ->toThrow(RuntimeException::class, 'Gemini connection failed');
});

it('logs retry and success for generateText', function () {
    $captured = [];
    Event::listen(MessageLogged::class, function (MessageLogged $e) use (&$captured) {
        $captured[] = ['level' => $e->level, 'message' => $e->message, 'context' => $e->context];
    });

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'ok']]],
            ]],
            'usageMetadata' => ['promptTokenCount' => 7, 'candidatesTokenCount' => 2],
        ], 200),
    ]);

    $client = new GeminiClient();
    $client->generateText('log me');

    $info = collect($captured)->first(fn ($c) => $c['message'] === 'Gemini text generation completed');
    expect($info)->not->toBeNull();
    expect($info['context']['input_tokens'])->toBe(7);
    expect($info['context']['output_tokens'])->toBe(2);
    expect($info['context']['attempts'])->toBe(1);
});

it('logs warning on connection timeout retry for generateText', function () {
    $captured = [];
    Event::listen(MessageLogged::class, function (MessageLogged $e) use (&$captured) {
        $captured[] = ['level' => $e->level, 'message' => $e->message, 'context' => $e->context];
    });

    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 2) {
            throw new ConnectionException('cURL error 28');
        }

        return Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'ok']]],
            ]],
        ], 200);
    });

    $client = new GeminiClient();
    $client->generateText('retry log');

    $warning = collect($captured)->first(fn ($c) => $c['message'] === 'Gemini transient HTTP error; retrying');
    expect($warning)->not->toBeNull();
    expect($warning['context']['status'])->toBe('connection');
    expect($warning['context']['attempt'])->toBe(1);
});

it('does not retry a permanent 4xx client error', function () {
    // A 400 (bad schema / refusal) is a permanent client error: it must throw on
    // the FIRST attempt, never entering the transient-retry branch that 429/5xx
    // and ConnectionException share.
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(['error' => ['code' => 400, 'message' => 'invalid schema']], 400);
    });

    $client = new GeminiClient();

    expect(fn () => $client->generateText('bad request'))
        ->toThrow(RuntimeException::class, 'Gemini HTTP 400');
    expect($attempts)->toBe(1);
});

it('retries on connection timeout during file extraction', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'gem').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake');

    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 3) {
            throw new ConnectionException('cURL error 28: Connection timed out');
        }

        return Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => json_encode(['result' => 'ok'])]]],
            ]],
        ], 200);
    });

    $client = new GeminiClient();
    $r = $client->extract('prompt', $tmp, ['type' => 'OBJECT', 'properties' => ['result' => ['type' => 'STRING']]]);

    expect($r['result'])->toBe('ok');
    expect($attempts)->toBe(3);

    @unlink($tmp);
});
