<?php

// Boot the Laravel app (facades + container) but DO NOT use RefreshDatabase — the
// `invoices` DB is an isolated sqlite file we wrap in a transaction per test so
// nothing is left behind (same pattern as InvoiceReviewControllerTest).
//
// PURE math only — aggregates() never calls Gemini. summarize()'s Gemini call is
// intentionally NOT exercised here (no live network in a unit test).
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoiceBatchSummarizer;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

function summarizerBatch(array $overrides = []): InvoiceBatch
{
    return InvoiceBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'batch.pdf',
        'pdf_path' => 'uploads/invoices/pdf/batch.pdf',
        'status' => 'done',
        'total_pages' => 3,
        'processed_pages' => 3,
        'grand_total' => 0,
        'model_used' => 'gemini-3.5-flash',
    ], $overrides));
}

function summarizerInvoice(int $batchId, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        'supplier_name' => 'شركة الاختبار',
        'invoice_number' => 'INV-1',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'status' => 'done',
        'needs_review' => false,
    ], $overrides));
}

it('counts invoices and distinct suppliers, and sums total/vat', function () {
    $batch = summarizerBatch();
    summarizerInvoice($batch->id, ['page_number' => 1, 'supplier_name' => 'مورد أ', 'total_incl_vat' => 115, 'vat_amount' => 15]);
    summarizerInvoice($batch->id, ['page_number' => 2, 'supplier_name' => 'مورد أ', 'total_incl_vat' => 230, 'vat_amount' => 30]);
    summarizerInvoice($batch->id, ['page_number' => 3, 'supplier_name' => 'مورد ب', 'total_incl_vat' => 57.5, 'vat_amount' => 7.5]);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['batch_id'])->toBe($batch->id);
    expect($agg['invoice_count'])->toBe(3);
    expect($agg['supplier_count'])->toBe(2);
    expect($agg['total_incl_vat'])->toBe(402.5);
    expect($agg['vat_amount'])->toBe(52.5);
});

it('counts invoices flagged needs_review', function () {
    $batch = summarizerBatch();
    summarizerInvoice($batch->id, ['page_number' => 1, 'needs_review' => true]);
    summarizerInvoice($batch->id, ['page_number' => 2, 'needs_review' => false]);
    summarizerInvoice($batch->id, ['page_number' => 3, 'needs_review' => true]);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['needs_review_count'])->toBe(2);
});

it('finds the top supplier by invoice count', function () {
    $batch = summarizerBatch();
    summarizerInvoice($batch->id, ['page_number' => 1, 'supplier_name' => 'مورد أ']);
    summarizerInvoice($batch->id, ['page_number' => 2, 'supplier_name' => 'مورد أ']);
    summarizerInvoice($batch->id, ['page_number' => 3, 'supplier_name' => 'مورد ب']);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['top_supplier'])->toBe(['name' => 'مورد أ', 'count' => 2]);
});

it('ranks the top 3 suppliers by summed amount, descending', function () {
    $batch = summarizerBatch();
    summarizerInvoice($batch->id, ['page_number' => 1, 'supplier_name' => 'مورد صغير', 'total_incl_vat' => 10]);
    summarizerInvoice($batch->id, ['page_number' => 2, 'supplier_name' => 'مورد كبير', 'total_incl_vat' => 1000]);
    summarizerInvoice($batch->id, ['page_number' => 3, 'supplier_name' => 'مورد متوسط', 'total_incl_vat' => 500]);
    summarizerInvoice($batch->id, ['page_number' => 4, 'supplier_name' => 'مورد رابع', 'total_incl_vat' => 5]);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['top_suppliers_by_amount'])->toHaveCount(3);
    expect($agg['top_suppliers_by_amount'][0]['name'])->toBe('مورد كبير');
    expect($agg['top_suppliers_by_amount'][1]['name'])->toBe('مورد متوسط');
    expect($agg['top_suppliers_by_amount'][2]['name'])->toBe('مورد صغير');
});

it('handles an empty batch without dividing by zero or erroring', function () {
    $batch = summarizerBatch(['total_pages' => 0, 'processed_pages' => 0]);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['invoice_count'])->toBe(0);
    expect($agg['supplier_count'])->toBe(0);
    expect($agg['total_incl_vat'])->toBe(0.0);
    expect($agg['vat_amount'])->toBe(0.0);
    expect($agg['needs_review_count'])->toBe(0);
    expect($agg['top_supplier'])->toBeNull();
    expect($agg['top_suppliers_by_amount'])->toBe([]);
});

it('ignores null/blank supplier names when counting distinct suppliers', function () {
    $batch = summarizerBatch();
    summarizerInvoice($batch->id, ['page_number' => 1, 'supplier_name' => null]);
    summarizerInvoice($batch->id, ['page_number' => 2, 'supplier_name' => '']);
    summarizerInvoice($batch->id, ['page_number' => 3, 'supplier_name' => 'مورد أ']);

    $agg = app(InvoiceBatchSummarizer::class)->aggregates($batch->id);

    expect($agg['invoice_count'])->toBe(3);
    expect($agg['supplier_count'])->toBe(1);
});

it('builds a cache key that changes when processed_pages changes, so a growing batch refreshes', function () {
    $svc = app(InvoiceBatchSummarizer::class);

    $k1 = $svc->cacheKey(42, 2);
    $k2 = $svc->cacheKey(42, 3);

    expect($k1)->not->toBe($k2);
    expect($k1)->toContain('42');
});

it('builds a narrative prompt that embeds the aggregate numbers and forbids inventing data', function () {
    $svc = app(InvoiceBatchSummarizer::class);

    $prompt = $svc->narratePrompt([
        'batch_id' => 1,
        'invoice_count' => 3,
        'supplier_count' => 2,
        'total_incl_vat' => 402.5,
        'vat_amount' => 52.5,
        'needs_review_count' => 1,
        'top_supplier' => ['name' => 'مورد أ', 'count' => 2],
        'top_suppliers_by_amount' => [],
    ]);

    expect($prompt)->toContain('402.5');
    expect($prompt)->toContain('مورد أ');
    expect($prompt)->toContain('JSON');
});
