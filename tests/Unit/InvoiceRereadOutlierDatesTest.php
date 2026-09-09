<?php

// Follow-up to the 2026-09-09 date fix: 74 invoices on the two installs carry a
// date that is neither in the batch's month nor a day/month swap (wrong year,
// month misread under an overprint). Those can't be inferred, but the scan is
// still on disk — so ask the model ONE narrow question per batch: "for these
// invoice numbers, copy the printed date verbatim". A re-read is accepted only
// if it lands inside the batch window; anything else stays flagged for a human.
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
    config()->set('services.gemini.key', 'test-key');
    config()->set('services.gemini.base_url', 'https://gen.example/v1beta');
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

function rereadBatch(): InvoiceBatch
{
    $pdf = public_path('uploads/invoices/pdf/reread-test.pdf');
    @mkdir(dirname($pdf), 0777, true);
    file_put_contents($pdf, '%PDF-1.4 fake');

    return InvoiceBatch::create([
        'user_id' => 1, 'original_filename' => 'g44.pdf', 'pdf_path' => 'uploads/invoices/pdf/reread-test.pdf',
        'status' => 'done', 'total_pages' => 1, 'processed_pages' => 1, 'grand_total' => 0,
    ]);
}

function rereadInvoice(int $batchId, string $no, string $date): Invoice
{
    $page = Invoice::where('batch_id', $batchId)->max('page_number') + 1;

    return Invoice::create([
        'batch_id' => $batchId, 'page_number' => $page, 'supplier_name' => 'مورد', 'supplier_tax_number' => '300000000000003',
        'invoice_number' => $no, 'invoice_date' => $date, 'amount_before_vat' => 100, 'vat_amount' => 15,
        'total_incl_vat' => 115, 'status' => 'done', 'needs_review' => false,
    ]);
}

it('re-reads only the outliers, accepts a date inside the batch window, rejects one outside', function () {
    $b = rereadBatch();
    foreach (['84386' => '2026-08-14', '84561' => '2026-08-15', '84731' => '2026-08-16', '84893' => '2026-08-17'] as $no => $d) {
        rereadInvoice($b->id, $no, $d);
    }
    $fixable = rereadInvoice($b->id, '2670343', '2026-05-25');   // printed 25-08-2026
    $stillBad = rereadInvoice($b->id, '123605', '2020-08-08');   // model reads garbage again
    $oldReal = rereadInvoice($b->id, 'NHD25439015', '2025-12-26'); // genuinely old invoice filed with August
    $swapShaped = rereadInvoice($b->id, '123260', '2026-02-02'); // answer parses to a swap of the truth → must be refused

    Http::fake([
        '*' => Http::response(['candidates' => [['content' => ['parts' => [[
            'text' => json_encode(['dates' => [
                ['invoice_number' => '2670343', 'invoice_date' => '25-08-2026'],
                ['invoice_number' => '123605', 'invoice_date' => '08 08 202'],
                ['invoice_number' => 'NHD25439015', 'invoice_date' => '26-Dec-25'],
                ['invoice_number' => '123260', 'invoice_date' => '2026-02-08'],
            ]]),
        ]]]]]], 200),
    ]);

    Artisan::call('invoices:reread-outlier-dates', ['--batch' => $b->id]);
    $out = Artisan::output();

    expect($fixable->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-08-25');
    expect($fixable->validation_notes)->toContain('أُعيدت قراءة التاريخ من الصورة');
    expect($stillBad->refresh()->invoice_date->format('Y-m-d'))->toBe('2020-08-08');
    expect((bool) $stillBad->needs_review)->toBeTrue();
    // Same date read back from the scan: confirmed, NOT flagged.
    expect($oldReal->refresh()->invoice_date->format('Y-m-d'))->toBe('2025-12-26');
    expect((bool) $oldReal->needs_review)->toBeFalse();
    expect($oldReal->validation_notes)->toContain('تم التأكد من التاريخ من الصورة');
    expect($swapShaped->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-02-02');
    expect((bool) $swapShaped->needs_review)->toBeTrue();
    expect($out)->toContain('fixed=1')->toContain('confirmed=1')->toContain('unresolved=2');

    // One call per batch, and only the outliers are named in it.
    Http::assertSentCount(1);
    Http::assertSent(function ($req) {
        $text = json_encode($req->data());

        return str_contains($text, '2670343') && str_contains($text, '123605') && ! str_contains($text, '84386');
    });
});

it('dry-run asks the model but writes nothing', function () {
    $b = rereadBatch();
    foreach (['84386' => '2026-08-14', '84561' => '2026-08-15', '84731' => '2026-08-16', '84893' => '2026-08-17'] as $no => $d) {
        rereadInvoice($b->id, $no, $d);
    }
    $fixable = rereadInvoice($b->id, '2670343', '2026-05-25');

    Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [[
        'text' => json_encode(['dates' => [['invoice_number' => '2670343', 'invoice_date' => '25-08-2026']]]),
    ]]]]]], 200)]);

    Artisan::call('invoices:reread-outlier-dates', ['--batch' => $b->id, '--dry-run' => true]);

    expect(Artisan::output())->toContain('[dry-run]')->toContain('2026-05-25 → 2026-08-25');
    expect($fixable->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-05-25');
});
