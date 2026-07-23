<?php

// Phase 1 — verifies the GeminiClient result cache + usage ledger: an identical
// extract() call is served from cache (no second HTTP call, no extra quota), and
// every call is logged to ai_usage_log with the right cache_hit flag.
uses(Tests\TestCase::class);

use App\Services\GeminiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.cache_enabled', true);

    foreach (['ai_extractions', 'ai_usage_log'] as $t) {
        Schema::dropIfExists($t);
    }
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
        $table->string('outcome', 10)->default('success');
        $table->string('status_code', 20)->nullable();
        $table->unsignedInteger('input_tokens')->default(0);
        $table->unsignedInteger('output_tokens')->default(0);
        $table->decimal('est_cost_usd', 12, 6)->default(0);
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamp('created_at')->nullable();
    });
});

afterEach(function () {
    foreach (['ai_extractions', 'ai_usage_log'] as $t) {
        Schema::dropIfExists($t);
    }
});

it('serves an identical extract() from cache without a second HTTP call', function () {
    $calls = 0;
    Http::fake(function () use (&$calls) {
        $calls++;

        return Http::response([
            'candidates' => [['content' => ['parts' => [['text' => '{"x": 1}']]]]],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 20],
        ], 200);
    });

    $tmp = tempnam(sys_get_temp_dir(), 'aicache').'.png';
    file_put_contents($tmp, 'PNGDATA-fixed-bytes');

    $client = new GeminiClient();
    $schema = ['type' => 'OBJECT', 'properties' => ['x' => ['type' => 'NUMBER']]];

    $first = $client->extract('prompt', $tmp, $schema, 'gemini-flash-lite-latest');
    $second = $client->extract('prompt', $tmp, $schema, 'gemini-flash-lite-latest');

    @unlink($tmp);

    expect($first)->toBe(['x' => 1]);
    expect($second)->toBe(['x' => 1]);
    expect($calls)->toBe(1);                          // second call hit the cache
    expect(DB::table('ai_extractions')->count())->toBe(1);
    expect((int) DB::table('ai_extractions')->value('hit_count'))->toBe(1);
    expect(DB::table('ai_usage_log')->count())->toBe(2);          // miss + hit both logged
    expect(DB::table('ai_usage_log')->where('cache_hit', true)->count())->toBe(1);
    // Cached hit still reports the stored token counts for the cost display.
    expect($client->lastInputTokens())->toBe(100);
});
