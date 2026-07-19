<?php

use App\Models\AiExtractionJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    if (! Schema::hasTable('ai_extraction_jobs')) {
        Schema::create('ai_extraction_jobs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('module')->nullable();
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->string('model')->nullable();
            $table->json('result_json')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
    DB::table('ai_extraction_jobs')->truncate();

    if (! Schema::hasTable('job_cat')) {
        Schema::create('job_cat', function ($table) {
            $table->unsignedBigInteger('j_c_id')->primary();
            $table->string('j_c_name_ar')->nullable();
        });
    }
    DB::table('job_cat')->insertOrIgnore(['j_c_id' => 1, 'j_c_name_ar' => 'مدير النظام']);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('emp_name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('username')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('emp_job')->nullable();
            $table->boolean('active')->default(true);
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });
    }
    DB::table('users')->truncate();
});

function makeUser(int $empJob = 2): User
{
    return User::factory()->create([
        'emp_job' => $empJob,
        'password' => bcrypt('password'),
    ]);
}

it('returns 404 when polling another users job via shop aiExtractStatus', function () {
    $owner = makeUser(2);
    $intruder = makeUser(2);

    $job = AiExtractionJob::create([
        'user_id' => $owner->id,
        'module' => 'shop',
        'status' => 'done',
        'file_path' => 'test.pdf',
        'file_url' => 'http://localhost/test.pdf',
        'result_json' => ['document_type' => 'license'],
    ]);

    $this->actingAs($intruder);
    $controller = new \App\Http\Controllers\Dashboard\ShopController;
    $response = $controller->aiExtractStatus($job->id);

    expect($response->status())->toBe(404);
    expect($response->getData(true))->toBe(['status' => false, 'message_out' => 'الطلب غير موجود']);
});

it('returns 404 when polling another users job via generic aiExtractStatus', function () {
    $owner = makeUser(2);
    $intruder = makeUser(2);

    $job = AiExtractionJob::create([
        'user_id' => $owner->id,
        'module' => 'shop',
        'status' => 'done',
        'file_path' => 'test.pdf',
        'file_url' => 'http://localhost/test.pdf',
        'result_json' => ['document_type' => 'license'],
    ]);

    $this->actingAs($intruder);
    $controller = new \App\Http\Controllers\Dashboard\AiExtractionController;
    $response = $controller->status($job->id);

    expect($response->status())->toBe(404);
    expect($response->getData(true))->toBe(['status' => false, 'message_out' => 'الطلب غير موجود']);
});

it('allows an admin to poll any job', function () {
    $owner = makeUser(2);
    $admin = makeUser(1);

    $job = AiExtractionJob::create([
        'user_id' => $owner->id,
        'module' => 'shop',
        'status' => 'done',
        'file_path' => 'test.pdf',
        'file_url' => 'http://localhost/test.pdf',
        'result_json' => ['document_type' => 'license'],
    ]);

    $this->actingAs($admin);
    $controller = new \App\Http\Controllers\Dashboard\AiExtractionController;
    $response = $controller->status($job->id);

    expect($response->status())->toBe(200);
    expect($response->getData(true)['status'])->toBeTrue();
});
