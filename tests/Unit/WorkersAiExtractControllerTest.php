<?php

// Boot the Laravel app (Http facade + container + real DB connection) but DO NOT use
// RefreshDatabase — same reasoning as InvoiceGeminiTest.php: the app's `migrate:fresh`
// is broken today by an unrelated duplicate-class migration bug (pre-existing,
// out of scope for Spec 004 B3), and this controller test only needs to read/write a
// couple of rows it cleans up itself, not a freshly migrated schema.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\WorkersController;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Spec 004 B3 forbids editing routes/dashboard.php directly here — the real route
// (POST /dashboard/workers/ai-extract -> workers.ai_extract) is reported separately
// for the orchestrator to add. This ad hoc route exercises the exact same controller
// action + its constructor middleware (ishaveaccess:2) without touching that file.
beforeEach(function () {
    Route::post('/test-only/workers/ai-extract', [WorkersController::class, 'aiExtract']);
});

function makeAdminUser(): User
{
    // users.emp_job has an FK to job_cat.j_c_id; ensure the admin job category row
    // exists (idempotent — safe to run concurrently with other test runs).
    DB::table('job_cat')->insertOrIgnore(['j_c_id' => 1, 'j_c_name_ar' => 'مدير النظام']);

    return User::factory()->create(['emp_job' => 1]);
}

it('extracts iqama/passport fields for the worker add form and audit-logs the extraction', function () {
    config()->set('services.gemini.key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'worker_name' => 'محمد أحمد',
                        'ssn' => '2456789012',
                        'passport_no' => 'A1234567',
                        'dob' => '1990-05-01',
                        'doe' => '2027-01-15',
                        'dop' => '2029-03-20',
                        'nationality' => null,
                        'field_confidence' => ['ssn' => 0.9, 'passport_no' => 0.8],
                    ]),
                ]]],
            ]],
        ], 200),
    ]);

    $file = UploadedFile::fake()->image('iqama.jpg', 800, 600);
    $user = makeAdminUser();

    try {
        $response = $this->actingAs($user)
            ->post('/test-only/workers/ai-extract', ['document' => $file]);

        $response->assertOk();
        $response->assertJson(['status' => true]);
        $response->assertJsonPath('data.worker_name', 'محمد أحمد');
        $response->assertJsonPath('data.ssn', '2456789012');
        $response->assertJsonPath('data.passport_no', 'A1234567');
        $response->assertJsonPath('data.dob', '1990-05-01');
        $response->assertJsonPath('data.doe', '2027-01-15');
        $response->assertJsonPath('data.dop', '2029-03-20');

        expect(DB::table('ai_audit_log')->where('document_type', 'worker')->where('action', 'extract')->count())->toBe(1);
    } finally {
        DB::table('ai_audit_log')->where('document_type', 'worker')->where('action', 'extract')->delete();
        $user->delete();
    }
});

it('rejects a request with no file', function () {
    $user = makeAdminUser();

    try {
        $response = $this->actingAs($user)
            ->post('/test-only/workers/ai-extract', []);

        $response->assertStatus(302)->assertSessionHasErrors('document');
    } finally {
        $user->delete();
    }
});
