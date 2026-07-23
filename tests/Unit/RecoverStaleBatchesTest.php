<?php

use App\Models\AiExtractionJob;
use App\Models\InvoiceBatch;
use App\Models\LeaseBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 013 bundle B1 — the ai:recover-stale-jobs sweep now covers the two bulk
 * pipelines (InvoiceBatch / LeaseBatch) in addition to the interactive
 * AiExtractionJob it always handled. A batch left in `processing` by a crashed
 * worker past the whole-batch deadline + buffer is marked `failed` (and its
 * still-in-flight child rows too); a fresh one is left strictly alone.
 *
 * Same two-independent-:memory:-sqlite-DB pattern as InvoiceBulkPushTest: the
 * MAIN connection (ai_extraction_jobs) and the isolated `invoices` connection
 * (invoice_batches/invoices/lease_batches/lease_extractions) are each their own
 * sqlite :memory: DB.
 */
beforeEach(function () {
    // Main (mysql in prod) connection -> sqlite :memory: for this run.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    // Isolated `invoices` connection -> its OWN sqlite :memory: DB.
    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    // Deterministic thresholds — a batch is stale after 3600 + 120 = 3720s idle.
    config()->set('services.gemini.batch_timeout', 3600);
    config()->set('services.gemini.page_timeout', 120);

    // ai_extraction_jobs lives on the DEFAULT connection.
    require_once base_path('database/migrations/2026_07_18_150000_create_ai_extraction_jobs_table.php');
    (new CreateAiExtractionJobsTable())->up();

    // Real invoices-side schema on the isolated connection.
    require_once base_path('database/migrations/invoices/2026_06_16_000001_create_invoice_batches_table.php');
    require_once base_path('database/migrations/invoices/2026_06_16_000002_create_invoices_table.php');
    require_once base_path('database/migrations/invoices/2026_06_23_000012_create_lease_staging_tables.php');
    (new CreateInvoiceBatchesTable())->up();
    (new CreateInvoicesTable())->up();
    (new CreateLeaseStagingTables())->up();
});

afterEach(function () {
    Mockery::close();
});

/** Insert a batch row on the isolated conn with an explicit updated_at; returns its id. */
function seedProcessingBatch(string $table, $updatedAt): int
{
    return DB::connection('invoices')->table($table)->insertGetId([
        'user_id' => 1,
        'original_filename' => 'x.pdf',
        'status' => 'processing',
        'total_pages' => 3,
        'processed_pages' => 0,
        'created_at' => $updatedAt,
        'updated_at' => $updatedAt,
    ]);
}

// ---- InvoiceBatch sweep -------------------------------------------------------

it('marks an old processing invoice batch (and its in-flight invoices) as failed', function () {
    $old = seedProcessingBatch('invoice_batches', now()->subHours(2)); // 7200s > 3720s -> stale
    DB::connection('invoices')->table('invoices')->insert([
        'batch_id' => $old, 'page_number' => 1, 'status' => 'pending',
        'needs_review' => 0, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2),
    ]);

    $this->artisan('ai:recover-stale-jobs')->assertSuccessful();

    $batch = InvoiceBatch::find($old);
    expect($batch->status)->toBe('failed');
    expect($batch->error_message)->not->toBeNull();

    $inv = DB::connection('invoices')->table('invoices')->where('batch_id', $old)->first();
    expect($inv->status)->toBe('failed');
    expect((bool) $inv->needs_review)->toBeTrue();
    expect($inv->error_message)->not->toBeNull();
});

it('leaves a fresh processing invoice batch untouched', function () {
    $fresh = seedProcessingBatch('invoice_batches', now()); // just started -> not stale

    $this->artisan('ai:recover-stale-jobs')->assertSuccessful();

    expect(InvoiceBatch::find($fresh)->status)->toBe('processing');
});

it('does not fail invoices that already finished (done stays done)', function () {
    $old = seedProcessingBatch('invoice_batches', now()->subHours(2));
    DB::connection('invoices')->table('invoices')->insert([
        'batch_id' => $old, 'page_number' => 1, 'status' => 'done',
        'needs_review' => 0, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2),
    ]);

    $this->artisan('ai:recover-stale-jobs')->assertSuccessful();

    $inv = DB::connection('invoices')->table('invoices')->where('batch_id', $old)->first();
    expect($inv->status)->toBe('done'); // finished rows are never clobbered
    expect(InvoiceBatch::find($old)->status)->toBe('failed');
});

// ---- LeaseBatch sweep ---------------------------------------------------------

it('marks an old processing lease batch as failed and leaves a fresh one alone', function () {
    $old = seedProcessingBatch('lease_batches', now()->subHours(2));
    $fresh = seedProcessingBatch('lease_batches', now());
    DB::connection('invoices')->table('lease_extractions')->insert([
        'batch_id' => $old, 'page_number' => 1, 'status' => 'pending',
        'needs_review' => 0, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2),
    ]);

    $this->artisan('ai:recover-stale-jobs')->assertSuccessful();

    expect(LeaseBatch::find($old)->status)->toBe('failed');
    expect(LeaseBatch::find($fresh)->status)->toBe('processing');

    $ext = DB::connection('invoices')->table('lease_extractions')->where('batch_id', $old)->first();
    expect($ext->status)->toBe('failed');
});

// ---- Regression: interactive AiExtractionJob sweep still fires ----------------

it('still recovers stale interactive AiExtractionJob rows', function () {
    $timeout = (new App\Jobs\ProcessInteractiveExtraction(0))->timeout; // 180s
    $staleId = DB::table('ai_extraction_jobs')->insertGetId([
        'user_id' => 1, 'module' => 'shop', 'status' => 'processing',
        'file_path' => '/tmp/x', 'created_at' => now()->subSeconds($timeout * 3),
        'updated_at' => now()->subSeconds($timeout * 3),
    ]);
    $freshId = DB::table('ai_extraction_jobs')->insertGetId([
        'user_id' => 1, 'module' => 'shop', 'status' => 'processing',
        'file_path' => '/tmp/y', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->artisan('ai:recover-stale-jobs')->assertSuccessful();

    expect(AiExtractionJob::find($staleId)->status)->toBe('failed');
    expect(AiExtractionJob::find($freshId)->status)->toBe('processing');
});
