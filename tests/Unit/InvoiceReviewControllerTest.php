<?php

// Boot the Laravel app (facades + container) but DO NOT use RefreshDatabase — the
// main DB is a local Docker MySQL and the `invoices` DB is an isolated sqlite file
// we wrap in a transaction per test so nothing is left behind.
uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Jobs\ProcessInvoiceBatch;
use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
    Mockery::close();
});

function actingAsAdmin(int $id = 1): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => 1]);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

function makeBatch(array $overrides = []): InvoiceBatch
{
    return InvoiceBatch::create(array_merge([
        'user_id' => 1,
        'original_filename' => 'test.pdf',
        'pdf_path' => 'uploads/invoices/pdf/test.pdf',
        'status' => 'done',
        'total_pages' => 1,
        'processed_pages' => 1,
        'grand_total' => 115,
    ], $overrides));
}

function makeInvoice(int $batchId, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'batch_id' => $batchId,
        'page_number' => 1,
        'supplier_name' => 'شركة الاختبار',
        'supplier_tax_number' => '300000000000003',
        'invoice_number' => 'INV-1',
        'invoice_date' => '2026-07-01',
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
        'status' => 'done',
        'needs_review' => true,
        'field_confidence' => ['supplier_name' => 0.5, 'invoice_number' => 0.95],
    ], $overrides));
}

// ---- review() ----------------------------------------------------------------

it('review() returns the batch with invoices, their line items, and field confidence', function () {
    actingAsAdmin();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id);
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'batch_id' => $batch->id,
        'line_no' => 1,
        'name' => 'أسمنت',
        'quantity' => 10,
        'unit_price' => 12.5,
        'line_total' => 125,
    ]);

    $controller = new InvoiceController();
    $view = $controller->review($batch->id);

    expect($view->getName())->toBe('dashboard.invoices.review');
    $data = $view->getData();
    expect($data['batch']->id)->toBe($batch->id);
    expect($data['invoices'])->toHaveCount(1);
    expect($data['invoices']->first()->field_confidence['invoice_number'])->toBe(0.95);
    expect($data['invoices']->first()->items)->toHaveCount(1);
});

it('review() aborts 403 for a non-admin who does not own the batch', function () {
    Auth::shouldReceive('user')->andReturn((object) ['id' => 9, 'emp_job' => 2]);
    Auth::shouldReceive('id')->andReturn(9);
    $batch = makeBatch(['user_id' => 5]);

    $controller = new InvoiceController();
    expect(fn () => $controller->review($batch->id))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});

// ---- approve() -----------------------------------------------------------------

it('approve() clears needs_review and logs the action', function () {
    actingAsAdmin();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id, ['needs_review' => true]);

    $controller = new InvoiceController();
    $response = $controller->approve($invoice->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    expect($invoice->fresh()->needs_review)->toBeFalse();
    expect(DB::table('ai_audit_log')->where('document_id', $invoice->id)->where('action', 'approve')->exists())->toBeTrue();
});

// ---- reject() --------------------------------------------------------------------

it('reject() sets status to rejected and logs the action', function () {
    actingAsAdmin();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id);

    $controller = new InvoiceController();
    $response = $controller->reject($invoice->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    expect($invoice->fresh()->status)->toBe('rejected');
    expect(DB::table('ai_audit_log')->where('document_id', $invoice->id)->where('action', 'reject')->exists())->toBeTrue();
});

// ---- reprocess() -------------------------------------------------------------------

it('reprocess() re-dispatches ProcessInvoiceBatch for the invoice batch and logs it', function () {
    actingAsAdmin();
    Queue::fake();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id);

    $controller = new InvoiceController();
    $response = $controller->reprocess($invoice->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    Queue::assertPushed(ProcessInvoiceBatch::class, fn ($job) => $job->batchId === $batch->id);
    expect(DB::table('ai_audit_log')->where('document_id', $invoice->id)->where('action', 'reprocess')->exists())->toBeTrue();
});

// ---- draft() ----------------------------------------------------------------------

it('draft() sets status to draft without finalizing the invoice', function () {
    actingAsAdmin();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id, ['needs_review' => true]);

    $controller = new InvoiceController();
    $response = $controller->draft($invoice->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    expect($invoice->fresh()->status)->toBe('draft');
    expect($invoice->fresh()->needs_review)->toBeTrue(); // still needs an explicit Approve
});

// ---- error() ----------------------------------------------------------------------

it('error() lists only failed invoices, scoped to one batch when given', function () {
    actingAsAdmin();
    $batch = makeBatch(['status' => 'failed']);
    $failed = makeInvoice($batch->id, ['status' => 'failed', 'error_message' => 'تعذرت القراءة']);
    $done = makeInvoice($batch->id, ['page_number' => 2, 'status' => 'done']);

    $controller = new InvoiceController();
    $view = $controller->error($batch->id);

    expect($view->getName())->toBe('dashboard.invoices.error');
    $data = $view->getData();
    $ids = $data['invoices']->pluck('id');
    expect($ids)->toContain($failed->id);
    expect($ids)->not->toContain($done->id);
});

// ---- manualEntry() ------------------------------------------------------------------

it('manualEntry() fills fields on a failed invoice but still requires explicit approval', function () {
    actingAsAdmin();
    $batch = makeBatch();
    $invoice = makeInvoice($batch->id, [
        'status' => 'failed',
        'supplier_name' => null,
        'invoice_number' => null,
        'total_incl_vat' => null,
        'error_message' => 'فشل الاستخراج',
    ]);

    $request = Request::create('/x', 'POST', [
        'supplier_name' => 'مورد يدوي',
        'invoice_number' => 'MAN-1',
        'invoice_date' => '2026-07-10',
        'amount_before_vat' => 200,
        'vat_amount' => 30,
        'total_incl_vat' => 230,
    ]);

    $controller = new InvoiceController();
    $response = $controller->manualEntry($request, $invoice->id);
    $payload = $response->getData(true);

    expect($payload['status'])->toBeTrue();
    $fresh = $invoice->fresh();
    expect($fresh->supplier_name)->toBe('مورد يدوي');
    expect($fresh->status)->toBe('done');
    expect($fresh->error_message)->toBeNull();
    expect($fresh->needs_review)->toBeTrue(); // manual entry alone never finalizes the record
});

// ---- report() ---------------------------------------------------------------------

it('report() aggregates counts, totals, and rejected/needs-review numbers', function () {
    actingAsAdmin();

    $controller = new InvoiceController();
    $before = $controller->report()->getData()['stats'];

    $batch = makeBatch();
    makeInvoice($batch->id, ['page_number' => 1, 'total_incl_vat' => 115, 'vat_amount' => 15, 'status' => 'done', 'needs_review' => false]);
    makeInvoice($batch->id, ['page_number' => 2, 'total_incl_vat' => 230, 'vat_amount' => 30, 'status' => 'rejected', 'needs_review' => false]);
    makeInvoice($batch->id, ['page_number' => 3, 'total_incl_vat' => 50, 'vat_amount' => 0, 'status' => 'done', 'needs_review' => true, 'validation_notes' => 'رقم مكرر']);

    $view = $controller->report();
    expect($view->getName())->toBe('dashboard.invoices.report');
    $after = $view->getData()['stats'];

    // Assert deltas rather than absolutes: the isolated `invoices` sqlite file
    // carries real dev data outside this test's own rows.
    expect(round($after['totalPurchases'] - $before['totalPurchases'], 2))->toBe(395.0);
    expect(round($after['totalVat'] - $before['totalVat'], 2))->toBe(45.0);
    expect($after['rejected'] - $before['rejected'])->toBe(1);
    expect($after['needsReview'] - $before['needsReview'])->toBe(1);
    expect($after['duplicates'] - $before['duplicates'])->toBe(1);
});
