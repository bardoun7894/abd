<?php

// Phase 3 — verifies the async interactive-extraction job: it reads the stored file,
// runs the module's extractor, and records the result (done) — or the error (failed).
uses(Tests\TestCase::class);

use App\Jobs\ProcessInteractiveExtraction;
use App\Models\AiExtractionJob;
use App\Services\DocumentStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
    config()->set('services.gemini.cache_enabled', false); // isolate the job from the cache layer

    foreach (['ai_extraction_jobs', 'ai_usage_log'] as $t) {
        Schema::dropIfExists($t);
    }
    Schema::create('ai_extraction_jobs', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('module', 40);
        $table->string('status', 20)->default('pending');
        $table->string('file_path', 1000);
        $table->string('file_url', 1000)->nullable();
        $table->string('model', 80)->nullable();
        $table->longText('result_json')->nullable();
        $table->text('error')->nullable();
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

    // Stub file storage so the job doesn't touch disk/encryption.
    $fake = Mockery::mock(DocumentStorage::class);
    $fake->shouldReceive('read')->andReturn(['contents' => 'fake-image-bytes', 'mime' => 'image/png']);
    app()->instance(DocumentStorage::class, $fake);
});

afterEach(function () {
    foreach (['ai_extraction_jobs', 'ai_usage_log'] as $t) {
        Schema::dropIfExists($t);
    }
    Mockery::close();
});

it('marks the job done and stores the extracted fields', function () {
    Http::fake([
        '*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => '{"document_type":"lease","document_number":"L-9","expiry_date":"2027-01-01","owner_name":"مالك","rent_amount":12000}']]]]],
            'usageMetadata' => ['promptTokenCount' => 50, 'candidatesTokenCount' => 10],
        ], 200),
    ]);

    $job = AiExtractionJob::create(['module' => 'shop', 'status' => 'pending', 'file_path' => 'x.png']);
    (new ProcessInteractiveExtraction($job->id))->handle();

    $job->refresh();
    expect($job->status)->toBe('done');
    expect($job->result_json['document_type'])->toBe('lease');
    expect($job->result_json['owner_name'])->toBe('مالك');
});

it('marks the job failed and records the error when extraction throws', function () {
    Http::fake(['*' => Http::response('nope', 500)]);
    config()->set('services.gemini.interactive_retries', 1); // fail fast

    $job = AiExtractionJob::create(['module' => 'shop', 'status' => 'pending', 'file_path' => 'x.png']);
    (new ProcessInteractiveExtraction($job->id))->handle();

    $job->refresh();
    expect($job->status)->toBe('failed');
    expect($job->error)->not->toBeNull();
});
