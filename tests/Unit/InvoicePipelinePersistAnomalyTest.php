<?php

// Verifies InvoicePipeline::persist() runs the InvoiceAnomalyDetector on the
// per-invoice extract path and appends its notes to validation_notes /
// needs_review, on top of whatever InvoiceExtractionService::validate() already
// produced. Boots Laravel + wraps the `invoices` connection in a transaction —
// same pattern as InvoiceReviewControllerTest.php (isolated sqlite file, never
// migrate:fresh'd in tests).
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoicePipeline;
use App\Services\InvoiceExtractionService;
use App\Services\PdfPageRasterizer;
use App\Services\PdfPageSplitter;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
    $this->pipeline = new InvoicePipeline(
        new PdfPageSplitter(),
        new InvoiceExtractionService(),
        new PdfPageRasterizer(),
    );
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

function persistTestBatch(): InvoiceBatch
{
    return InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'persist-test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/persist-test.pdf',
        'status' => 'processing',
        'total_pages' => 1,
        'processed_pages' => 0,
        'grand_total' => 0,
    ]);
}

it('appends an anomaly note and sets needs_review when persisting an invoice with a future date', function () {
    $batch = persistTestBatch();

    $invoice = $this->pipeline->persist($batch, 1, [
        'supplier_name' => 'مورد اختبار الدمج',
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'PIPE-1',
        'invoice_date' => now()->addDays(10)->toDateString(),
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'needs_review' => false,
        'validation_notes' => [],
    ], null);

    expect($invoice->needs_review)->toBeTrue();
    expect($invoice->validation_notes)->toContain('شذوذ');
    expect($invoice->validation_notes)->toContain('المستقبل');
});

it('keeps existing validate() notes and appends anomaly notes without overwriting them', function () {
    $batch = persistTestBatch();

    $invoice = $this->pipeline->persist($batch, 1, [
        'supplier_name' => null, // triggers validate()'s "missing field" note
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'PIPE-2',
        'invoice_date' => now()->subDays(5)->toDateString(),
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'needs_review' => true,
        'validation_notes' => ['حقل مفقود: supplier_name'],
    ], null);

    expect($invoice->validation_notes)->toContain('حقل مفقود');
    expect($invoice->needs_review)->toBeTrue();
});

it('does not add anomaly notes or flip needs_review for a clean invoice', function () {
    $batch = persistTestBatch();

    $invoice = $this->pipeline->persist($batch, 1, [
        'supplier_name' => 'مورد اختبار نظيف '.uniqid(),
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'PIPE-3',
        'invoice_date' => now()->subDays(2)->toDateString(),
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'needs_review' => false,
        'validation_notes' => [],
    ], null);

    expect($invoice->needs_review)->toBeFalse();
    expect((string) $invoice->validation_notes)->toBe('');
});
