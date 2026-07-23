<?php

// Spec 013 bundle B4 — AI dashboard: errors + per-user.
// SettingsController::aiUsage previously showed cost/tokens/cache-hit by module/day
// but no failure count and no 429 rate, because ai_usage_log only ever recorded
// successful calls. This covers:
//  - GeminiClient::logAiFailure() writes a 'failure' row (zero tokens/cost) with the
//    right status_code when generateText()/extract() exhaust retries (HTTP and
//    ConnectionException paths).
//  - A successful call is still recorded as outcome='success' (DB column default).
//  - SettingsController::aiUsage aggregates failure_calls/failure_rate/
//    rate_limited_calls/rate_limit_rate at the top level, and a per-module /
//    per-user 'failures' breakdown, without disturbing the pre-existing
//    calls/hits/tokens/cost numbers (which stay scoped to successful calls).
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\SettingsController;
use App\Models\AiSubscription;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.cache_enabled', false);
    config()->set('services.gemini.usd_to_sar', 3.75);

    // Force the default connection to an isolated sqlite :memory: DB (same pattern
    // as InvoiceBulkPushTest) — never touch the real mysql `laravel_testing` DB, and
    // this environment may not even have a mysql server reachable.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

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
    AiSubscription::create(['active' => true, 'expires_at' => now()->addYear(), 'quota_pages' => 1000]);

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

    // Mirrors the real migrations: create + the B4 add_outcome_to_ai_usage_log_table.
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

// ---- GeminiClient::logAiFailure() ---------------------------------------------

it('logs a failure row with status_code 429 when generateText exhausts retries', function () {
    config()->set('services.gemini.retries', 2);
    Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);

    $client = new GeminiClient();

    expect(fn () => $client->generateText('will fail'))
        ->toThrow(RuntimeException::class, 'Gemini HTTP 429');

    expect(DB::table('ai_usage_log')->count())->toBe(1);
    $row = DB::table('ai_usage_log')->first();
    expect($row->outcome)->toBe('failure')
        ->and($row->status_code)->toBe('429')
        ->and((bool) $row->cache_hit)->toBeFalse()
        ->and((int) $row->input_tokens)->toBe(0)
        ->and((float) $row->est_cost_usd)->toBe(0.0);
});

it('logs a failure row with status_code connection when ConnectionException retries are exhausted', function () {
    config()->set('services.gemini.retries', 2);
    Http::fake(function () {
        throw new ConnectionException('cURL error 28: Connection timed out');
    });

    $client = new GeminiClient();

    expect(fn () => $client->generateText('will fail too'))
        ->toThrow(RuntimeException::class, 'Gemini connection failed');

    $row = DB::table('ai_usage_log')->first();
    expect($row->outcome)->toBe('failure')
        ->and($row->status_code)->toBe('connection');
});

it('logs a failure row for extract() (file path) when retries are exhausted', function () {
    config()->set('services.gemini.retries', 2);
    Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

    $tmp = tempnam(sys_get_temp_dir(), 'ai').'.png';
    file_put_contents($tmp, 'bytes');

    $client = new GeminiClient();
    $schema = ['type' => 'OBJECT', 'properties' => ['x' => ['type' => 'NUMBER']]];

    expect(fn () => $client->extract('prompt', $tmp, $schema))
        ->toThrow(RuntimeException::class, 'Gemini HTTP 500');

    @unlink($tmp);

    $row = DB::table('ai_usage_log')->first();
    expect($row->outcome)->toBe('failure')
        ->and($row->status_code)->toBe('500');
});

it('still records a successful call as outcome success', function () {
    Http::fake(['*' => Http::response([
        'candidates' => [['content' => ['parts' => [['text' => 'ok']]]]],
        'usageMetadata' => ['promptTokenCount' => 3, 'candidatesTokenCount' => 1],
    ], 200)]);

    $client = new GeminiClient();
    $client->generateText('fine');

    $row = DB::table('ai_usage_log')->first();
    expect($row->outcome)->toBe('success');
});

// ---- SettingsController::aiUsage aggregation -----------------------------------

function seedUsageRow(array $overrides = []): void
{
    DB::table('ai_usage_log')->insert(array_merge([
        'module' => 'invoice',
        'model' => 'gemini-flash-lite-latest',
        'cache_hit' => false,
        'outcome' => 'success',
        'status_code' => null,
        'input_tokens' => 100,
        'output_tokens' => 50,
        'est_cost_usd' => 0.01,
        'user_id' => 1,
        'created_at' => now(),
    ], $overrides));
}

it('aiUsage reports failure_calls/failure_rate/rate_limited_calls/rate_limit_rate', function () {
    // 2 successful invoice calls.
    seedUsageRow();
    seedUsageRow(['module' => 'invoice']);
    // 1 rate-limited failure, 1 other-error failure — both zero cost/tokens.
    seedUsageRow(['module' => 'invoice', 'outcome' => 'failure', 'status_code' => '429', 'input_tokens' => 0, 'output_tokens' => 0, 'est_cost_usd' => 0]);
    seedUsageRow(['module' => 'lease', 'outcome' => 'failure', 'status_code' => '500', 'input_tokens' => 0, 'output_tokens' => 0, 'est_cost_usd' => 0]);

    $admin = User::create(['name' => 'Admin', 'email' => 'a@test', 'emp_job' => 1]);
    $this->actingAs($admin);

    $view = (new SettingsController())->aiUsage(new \Illuminate\Http\Request());
    $stats = $view->getData()['stats'];

    // Pre-existing metrics stay scoped to the 2 successful rows only.
    expect($stats['total_calls'])->toBe(2)
        ->and($stats['input_tokens'])->toBe(200)
        ->and($stats['cost_usd'])->toBe(0.02);

    // New failure/429 metrics.
    expect($stats['failure_calls'])->toBe(2)
        ->and($stats['total_attempts'])->toBe(4)
        ->and($stats['failure_rate'])->toBe(50.0)
        ->and($stats['rate_limited_calls'])->toBe(1)
        ->and($stats['rate_limit_rate'])->toBe(25.0);

    $byModule = $view->getData()['byModule']->keyBy('module');
    expect((int) $byModule['invoice']->calls)->toBe(2)
        ->and((int) $byModule['invoice']->failures)->toBe(1)
        ->and((int) $byModule['lease']->calls)->toBe(0)
        ->and((int) $byModule['lease']->failures)->toBe(1);
});

it('aiUsage reports per-user failures alongside per-user spend', function () {
    seedUsageRow(['user_id' => 7]);
    seedUsageRow(['user_id' => 7, 'outcome' => 'failure', 'status_code' => '429', 'input_tokens' => 0, 'output_tokens' => 0, 'est_cost_usd' => 0]);
    seedUsageRow(['user_id' => 9]);

    $admin = User::create(['id' => 7, 'name' => 'Employee Seven', 'email' => 'e7@test', 'emp_job' => 0]);
    User::create(['id' => 9, 'name' => 'Employee Nine', 'email' => 'e9@test', 'emp_job' => 0]);
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'a@test', 'emp_job' => 1]));

    $view = (new SettingsController())->aiUsage(new \Illuminate\Http\Request());
    $byUser = $view->getData()['byUser']->keyBy('user_id');

    expect((int) $byUser[7]->calls)->toBe(1)
        ->and((int) $byUser[7]->failures)->toBe(1)
        ->and((int) $byUser[9]->calls)->toBe(1)
        ->and((int) $byUser[9]->failures)->toBe(0);
});

it('empty state (no rows) reports zeroed failure/429 stats without error', function () {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'a@test', 'emp_job' => 1]));

    $view = (new SettingsController())->aiUsage(new \Illuminate\Http\Request());
    $stats = $view->getData()['stats'];

    expect($stats['total_calls'])->toBe(0)
        ->and($stats['failure_calls'])->toBe(0)
        ->and($stats['failure_rate'])->toBe(0.0)
        ->and($stats['rate_limited_calls'])->toBe(0)
        ->and($stats['rate_limit_rate'])->toBe(0.0);
});
