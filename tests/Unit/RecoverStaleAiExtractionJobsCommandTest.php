<?php

// Regression test for ai:recover-stale-jobs — processing rows older than twice
// the job timeout are marked failed; fresh rows and other statuses are untouched.
uses(Tests\TestCase::class);

use App\Models\AiExtractionJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('ai_extraction_jobs');
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
});

afterEach(function () {
    Schema::dropIfExists('ai_extraction_jobs');
});

it('marks stale processing ai extraction jobs as failed and leaves fresh rows alone', function () {
    $stale = AiExtractionJob::create([
        'module' => 'shop',
        'status' => 'processing',
        'file_path' => 'old.png',
    ]);
    DB::table('ai_extraction_jobs')->where('id', $stale->id)->update([
        'updated_at' => now()->subSeconds(500),
    ]);

    $fresh = AiExtractionJob::create([
        'module' => 'shop',
        'status' => 'processing',
        'file_path' => 'fresh.png',
    ]);

    $done = AiExtractionJob::create([
        'module' => 'shop',
        'status' => 'done',
        'file_path' => 'done.png',
    ]);
    DB::table('ai_extraction_jobs')->where('id', $done->id)->update([
        'updated_at' => now()->subSeconds(500),
    ]);

    $pending = AiExtractionJob::create([
        'module' => 'shop',
        'status' => 'pending',
        'file_path' => 'pending.png',
    ]);
    DB::table('ai_extraction_jobs')->where('id', $pending->id)->update([
        'updated_at' => now()->subSeconds(500),
    ]);

    Artisan::call('ai:recover-stale-jobs');

    $stale->refresh();
    $fresh->refresh();
    $done->refresh();
    $pending->refresh();

    expect($stale->status)->toBe('failed');
    expect($stale->error)->toBe('تجاوزت مهمة الاستخراج الوقت المحدد ولم تُكمل (انتهت صلاحية المهمة).');

    expect($fresh->status)->toBe('processing');
    expect($fresh->error)->toBeNull();

    expect($done->status)->toBe('done');
    expect($pending->status)->toBe('pending');
});
