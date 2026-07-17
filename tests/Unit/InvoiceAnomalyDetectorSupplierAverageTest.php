<?php

// Rule 6 (supplier-average deviation) needs real rows on the `invoices` connection,
// so this file boots Laravel and wraps each test in a transaction — same pattern
// used by InvoiceReviewControllerTest.php — instead of RefreshDatabase (the
// `invoices` DB is an isolated sqlite file, never migrate:fresh'd in tests).
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoiceAnomalyDetector;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
    $this->det = new InvoiceAnomalyDetector();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

function anomalyBatch(): InvoiceBatch
{
    return InvoiceBatch::create([
        'user_id' => 1,
        'original_filename' => 'anomaly-test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/anomaly-test.pdf',
        'status' => 'done',
        'total_pages' => 1,
        'processed_pages' => 1,
        'grand_total' => 0,
    ]);
}

function anomalySeedInvoice(int $batchId, int $page, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => $page,
        'supplier_name' => 'مورد ثابت للاختبار الإحصائي',
        'supplier_tax_number' => '300011122233003',
        'invoice_number' => 'HIST-'.$page,
        'invoice_date' => '2026-01-01',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 100,
        'status' => 'done',
        'needs_review' => false,
    ], $overrides));
}

it('flags a total far above the supplier historical average when there are >= 3 priors', function () {
    $batch = anomalyBatch();
    anomalySeedInvoice($batch->id, 1, ['total_incl_vat' => 100]);
    anomalySeedInvoice($batch->id, 2, ['total_incl_vat' => 100]);
    anomalySeedInvoice($batch->id, 3, ['total_incl_vat' => 100]);

    $notes = $this->det->detect([
        'supplier_name' => 'مورد ثابت للاختبار الإحصائي',
        'supplier_tax_number' => '300011122233003',
        'amount_before_vat' => 4000,
        'vat_amount' => 600,
        'total_incl_vat' => 4600, // 46x the 100 average
        'invoice_date' => '2026-06-01',
    ], 'invoices');

    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'متوسط فواتير هذا المورد')))->toBeTrue();
});

it('flags a total far below the supplier historical average when there are >= 3 priors', function () {
    $batch = anomalyBatch();
    anomalySeedInvoice($batch->id, 1, ['total_incl_vat' => 1000]);
    anomalySeedInvoice($batch->id, 2, ['total_incl_vat' => 1000]);
    anomalySeedInvoice($batch->id, 3, ['total_incl_vat' => 1000]);

    $notes = $this->det->detect([
        'supplier_name' => 'مورد ثابت للاختبار الإحصائي',
        'supplier_tax_number' => '300011122233003',
        'amount_before_vat' => 20,
        'vat_amount' => 3,
        'total_incl_vat' => 23, // far below 0.25x the 1000 average
        'invoice_date' => '2026-06-01',
    ], 'invoices');

    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'متوسط فواتير هذا المورد')))->toBeTrue();
});

it('does not flag when there are fewer than 3 prior invoices for the supplier', function () {
    $batch = anomalyBatch();
    anomalySeedInvoice($batch->id, 1, ['total_incl_vat' => 100]);
    anomalySeedInvoice($batch->id, 2, ['total_incl_vat' => 100]);

    $notes = $this->det->detect([
        'supplier_name' => 'مورد ثابت للاختبار الإحصائي',
        'supplier_tax_number' => '300011122233003',
        'amount_before_vat' => 4000,
        'vat_amount' => 600,
        'total_incl_vat' => 4600,
        'invoice_date' => '2026-06-01',
    ], 'invoices');

    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'متوسط فواتير هذا المورد')))->toBeFalse();
});

it('does not flag when the total is within the normal range of the supplier average', function () {
    $batch = anomalyBatch();
    anomalySeedInvoice($batch->id, 1, ['total_incl_vat' => 100]);
    anomalySeedInvoice($batch->id, 2, ['total_incl_vat' => 110]);
    anomalySeedInvoice($batch->id, 3, ['total_incl_vat' => 90]);

    $notes = $this->det->detect([
        'supplier_name' => 'مورد ثابت للاختبار الإحصائي',
        'supplier_tax_number' => '300011122233003',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 105,
        'invoice_date' => '2026-06-01',
    ], 'invoices');

    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'متوسط فواتير هذا المورد')))->toBeFalse();
});
