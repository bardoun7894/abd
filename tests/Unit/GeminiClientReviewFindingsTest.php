<?php

// Code-review fixes for GeminiClient billing/ledger accuracy:
// - multi-page extract() records real page count
// - cache-hit rows carry zero cost and dashboard excludes them from spend
// - generateText() respects concurrency cap and writes to ai_usage_log
// - 429 responses honor Retry-After / retryDelay
// - thought-only responses return empty string instead of leaking reasoning.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\SettingsController;
use App\Models\AiSubscription;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.cache_enabled', true);
    config()->set('services.gemini.max_concurrent', 2);
    config()->set('services.gemini.usd_to_sar', 3.75);

    foreach (['ai_subscriptions', 'ai_extractions', 'ai_usage_log', 'users'] as $t) {
        Schema::dropIfExists($t);
    }

    Schema::create('ai_subscriptions', function ($t) {
        $t->id();
        $t->boolean('active')->default(true);
        $t->date('starts_at')->nullable();
        $t->date('expires_at')->nullable();
        $t->unsignedInteger('quota_pages')->nullable();
        $t->unsignedInteger('used_pages')->default(0);
        $t->dateTime('renewed_at')->nullable();
        $t->timestamps();
    });
    AiSubscription::create(['active' => true, 'expires_at' => now()->addYear(), 'quota_pages' => 100]);

    Schema::create('ai_extractions', function ($table) {
        $table->id();
        $table->string('cache_key', 64)->unique();
        $table->string('module', 60)->nullable();
        $table->string('model', 80)->nullable();
        $table->string('file_hash', 64)->nullable();
        $table->longText('result_json');
        $table->unsignedInteger('input_tokens')->default(0);
        $table->unsignedInteger('output_tokens')->default(0);
        $table->decimal('est_cost_usd', 12, 6)->default(0);
        $table->unsignedInteger('hit_count')->default(0);
        $table->timestamps();
    });

    Schema::create('ai_usage_log', function ($table) {
        $table->id();
        $table->string('module', 60)->nullable();
        $table->string('model', 80)->nullable();
        $table->boolean('cache_hit')->default(false);
        $table->unsignedInteger('input_tokens')->default(0);
        $table->unsignedInteger('output_tokens')->default(0);
        $table->decimal('est_cost_usd', 12, 6)->default(0);
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('users', function ($t) {
        $t->id();
        $t->string('name')->nullable();
        $t->string('emp_name')->nullable();
        $t->string('email')->nullable();
        $t->string('password')->nullable();
        $t->unsignedInteger('emp_job')->default(0);
        $t->rememberToken();
        $t->timestamps();
    });
});

afterEach(function () {
    foreach (['ai_subscriptions', 'ai_extractions', 'ai_usage_log', 'users'] as $t) {
        Schema::dropIfExists($t);
    }
});

function buildSchema(): array
{
    return ['type' => 'OBJECT', 'properties' => ['x' => ['type' => 'NUMBER']]];
}

function successfulExtractionResponse(): array
{
    return [
        'candidates' => [['content' => ['parts' => [['text' => '{"x": 1}']]]]],
        'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 20],
    ];
}

it('records the real page count for multi-page extract calls', function () {
    Http::fake(['*' => Http::response(successfulExtractionResponse(), 200)]);

    $files = [];
    for ($i = 0; $i < 3; $i++) {
        $tmp = tempnam(sys_get_temp_dir(), 'ai').'.png';
        file_put_contents($tmp, "page{$i}");
        $files[] = $tmp;
    }

    $client = new GeminiClient();
    $result = $client->extract('prompt', $files, buildSchema());

    foreach ($files as $f) {
        @unlink($f);
    }

    expect($result)->toBe(['x' => 1])
        ->and(AiSubscription::current()->used_pages)->toBe(3);
});

it('logs cache hits at zero cost and dashboard excludes them from spend', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [['content' => ['parts' => [['text' => '{"x": 1}']]]]],
        'usageMetadata' => ['promptTokenCount' => 1000, 'candidatesTokenCount' => 200],
    ], 200)]);

    $tmp = tempnam(sys_get_temp_dir(), 'ai').'.png';
    file_put_contents($tmp, 'same-content');

    $client = new GeminiClient();
    $schema = buildSchema();

    $client->extract('prompt', $tmp, $schema); // miss
    $client->extract('prompt', $tmp, $schema); // hit

    @unlink($tmp);

    $miss = DB::table('ai_usage_log')->where('cache_hit', false)->first();
    $hit = DB::table('ai_usage_log')->where('cache_hit', true)->first();

    expect(DB::table('ai_usage_log')->count())->toBe(2)
        ->and((float) $hit->est_cost_usd)->toBe(0.0)
        ->and((float) $miss->est_cost_usd)->toBeGreaterThan(0);

    $admin = User::create(['name' => 'Admin', 'email' => 'a@test', 'emp_job' => 1]);
    $this->actingAs($admin);

    $controller = new SettingsController();
    $view = $controller->aiUsage(new \Illuminate\Http\Request());
    $stats = $view->getData()['stats'];

    expect($stats['total_calls'])->toBe(2)
        ->and($stats['hits'])->toBe(1)
        ->and($stats['cost_usd'])->toBe(round((float) $miss->est_cost_usd, 4));

    $byModule = $view->getData()['byModule']->first();
    expect((float) $byModule->cost)->toBe((float) $miss->est_cost_usd);

    $byDay = $view->getData()['byDay']->first();
    expect((float) $byDay->cost)->toBe((float) $miss->est_cost_usd);
});

it('meters generateText calls with a ledger entry', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [['content' => ['parts' => [['text' => 'hello']]]]],
        'usageMetadata' => ['promptTokenCount' => 10, 'candidatesTokenCount' => 5],
    ], 200)]);

    $client = new GeminiClient();
    $text = $client->generateText('say hi');

    expect($text)->toBe('hello')
        ->and(DB::table('ai_usage_log')->where('cache_hit', false)->count())->toBe(1);

    $row = DB::table('ai_usage_log')->first();
    expect($row->input_tokens)->toBe(10)
        ->and($row->output_tokens)->toBe(5)
        ->and($row->model)->not->toBeNull()
        ->and((float) $row->est_cost_usd)->toBeGreaterThanOrEqual(0);
});

it('rejects generateText when concurrency slots are exhausted', function () {
    config()->set('services.gemini.max_concurrent', 1);
    Cache::add('ai_slot_0', 1, 90);

    $client = new GeminiClient();
    expect(fn () => $client->generateText('say hi'))
        ->toThrow(RuntimeException::class, 'النظام مشغول');
});

it('parses Retry-After header for 429 backoff', function () {
    $client = new GeminiClient();
    $method = new ReflectionMethod($client, 'retryAfterSeconds');

    Http::fake(['*' => Http::response('', 429, ['Retry-After' => '5'])]);
    $resp = Http::post('https://example.com');
    expect($method->invoke($client, $resp))->toBe(5);
});

it('parses nested JSON retryDelay for 429 backoff', function () {
    $client = new GeminiClient();
    $method = new ReflectionMethod($client, 'retryAfterSeconds');

    Http::fake(['*' => Http::response(json_encode(['error' => ['retryDelay' => '12s']]), 429)]);
    $resp = Http::post('https://example.com');
    expect($method->invoke($client, $resp))->toBe(12);
});

it('parses top-level retryDelaySeconds for 429 backoff', function () {
    $client = new GeminiClient();
    $method = new ReflectionMethod($client, 'retryAfterSeconds');

    Http::fake(['*' => Http::response(json_encode(['retryDelaySeconds' => 7]), 429)]);
    $resp = Http::post('https://example.com');
    expect($method->invoke($client, $resp))->toBe(7);
});

it('returns empty string when response contains only thought parts', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [[
            'content' => [
                'parts' => [
                    ['text' => 'internal reasoning', 'thought' => true],
                ],
            ],
        ]],
        'usageMetadata' => ['promptTokenCount' => 5, 'candidatesTokenCount' => 3],
    ], 200)]);

    $client = new GeminiClient();
    $text = $client->generateText('think');

    expect($text)->toBe('')
        ->and(DB::table('ai_usage_log')->count())->toBe(1);
});
