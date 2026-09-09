<?php

// Client report 2026-09-09 «تسع فواتير مضافة، يطلعوا ستة في البحث». Two shapes of
// wrong invoice_date came out of one batch:
//   (a) day/month swapped — 04-08-2026 stored as 2026-04-08 (model read MM/DD);
//   (b) OCR misread — 25-08-2026 stored as 2026-05-25 (08 → 05 under an overprint).
// Batch context resolves both: (a) is auto-corrected when the swapped reading lands
// in the batch's dominant month; (b) is flagged needs_review so it cannot be pushed
// with a wrong date. The same helper drives the repair command for existing rows.
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoiceExtractionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::connection('invoices')->beginTransaction();
});

afterEach(function () {
    DB::connection('invoices')->rollBack();
});

function dateOutlierBatch(array $overrides = []): InvoiceBatch
{
    return InvoiceBatch::create(array_merge([
        'user_id' => 1, 'original_filename' => 'g44.pdf', 'pdf_path' => 'uploads/invoices/pdf/g44.pdf',
        'status' => 'done', 'total_pages' => 1, 'processed_pages' => 1, 'grand_total' => 0,
    ], $overrides));
}

function dateOutlierInvoice(int $batchId, string $no, ?string $date, array $overrides = []): Invoice
{
    // (batch_id, page_number, seq) is unique — one invoice per page here.
    $page = Invoice::where('batch_id', $batchId)->max('page_number') + 1;

    return Invoice::create(array_merge([
        'batch_id' => $batchId, 'page_number' => $page, 'supplier_name' => 'مورد', 'supplier_tax_number' => '300000000000003',
        'invoice_number' => $no, 'invoice_date' => $date, 'amount_before_vat' => 100, 'vat_amount' => 15,
        'total_incl_vat' => 115, 'status' => 'done', 'needs_review' => false,
    ], $overrides));
}

// ---------------------------------------------------------------- pure helper

it('finds the dominant month from the unambiguous dates and swaps the ambiguous strays into it', function () {
    // Batch 195 shape: sequential August invoices, four with day<=12 stored month-first.
    $dates = [
        1 => '2026-04-08', 2 => '2026-08-03', 3 => '2026-05-08', 4 => '2026-08-18', 5 => '2026-08-17',
        6 => '2026-08-15', 7 => '2026-08-16', 8 => '2026-08-14', 9 => '2026-08-13', 10 => '2026-07-08',
    ];
    $r = InvoiceExtractionService::dateOutliers($dates);

    expect($r['dominant'])->toBe('2026-08');
    expect($r['swap'])->toBe([1 => '2026-08-04', 3 => '2026-08-05', 10 => '2026-08-07']);
    expect($r['outlier'])->toBe([]);
});

it('flags a date that cannot be explained by a swap as an outlier', function () {
    // Batch 198 shape: 2670343 printed 25-08 but read as 05-25 — day 25 cannot be a month.
    $dates = [8 => '2026-05-25', 9 => '2026-08-25', 10 => '2026-08-20', 11 => '2026-08-24', 12 => '2026-08-15'];
    $r = InvoiceExtractionService::dateOutliers($dates);

    expect($r['dominant'])->toBe('2026-08');
    expect($r['swap'])->toBe([]);
    expect($r['outlier'])->toBe([8 => '2026-05-25']);
});

it('does nothing when there is no clear dominant month or too few dates', function () {
    expect(InvoiceExtractionService::dateOutliers([1 => '2026-04-08', 2 => '2026-08-03']))
        ->toBe(['dominant' => null, 'swap' => [], 'outlier' => []]);
    // Two months evenly split — no majority, leave everything alone.
    expect(InvoiceExtractionService::dateOutliers([
        1 => '2026-07-20', 2 => '2026-07-21', 3 => '2026-07-22', 4 => '2026-08-20', 5 => '2026-08-21', 6 => '2026-08-22',
    ]))->toBe(['dominant' => null, 'swap' => [], 'outlier' => []]);
});

it('tolerates end-of-previous-month dates in a batch, but not a wrong year or a date months off', function () {
    $dates = [
        1 => '2026-08-14', 2 => '2026-08-15', 3 => '2026-08-16', 4 => '2026-08-17',
        11 => '2026-08-18', 12 => '2026-08-19', 13 => '2026-08-20', 14 => '2026-08-21',
        5 => '2026-07-26',   // last days of July scanned with August — normal
        6 => '2026-07-31',
        7 => '2026-09-05',   // a few days into the next month — normal
        8 => '2026-06-12',   // 50 days before August — misread
        9 => '2020-08-25',   // wrong year
        10 => '2015-03-26',
    ];
    $r = InvoiceExtractionService::dateOutliers($dates);
    expect($r['dominant'])->toBe('2026-08');
    expect(array_keys($r['outlier']))->toBe([8, 9, 10]);
});

it('with no majority, a fallback month anchors the window but never produces swaps', function () {
    // Batch 214 shape: 8 unclear pages, dates all over the place, uploaded in September.
    $dates = [1 => '2025-08-28', 2 => '2025-09-01', 3 => '2024-08-31', 4 => '2024-06-23', 5 => '2026-02-28', 6 => '2025-03-24', 7 => '2026-09-08', 8 => '2026-04-09'];
    expect(InvoiceExtractionService::dateOutliers($dates))->toBe(['dominant' => null, 'swap' => [], 'outlier' => []]);

    $r = InvoiceExtractionService::dateOutliers($dates, '2026-09');
    expect($r['dominant'])->toBe('2026-09');
    expect($r['swap'])->toBe([]);                            // 2026-04-09 is swap-shaped, but not on a fallback
    expect(array_keys($r['outlier']))->toBe([1, 2, 3, 4, 5, 6, 8]);
});

it('ignores null dates and a date already in the dominant month with day<=12', function () {
    $dates = [1 => '2026-08-03', 2 => null, 3 => '2026-08-18', 4 => '2026-08-17', 5 => '2026-08-15', 6 => '2026-08-16'];
    $r = InvoiceExtractionService::dateOutliers($dates);
    expect($r['swap'])->toBe([])->and($r['outlier'])->toBe([]);
});

// ---------------------------------------------------------------- pipeline pass

it('the pipeline corrects swaps and flags outliers on the batch after extraction', function () {
    $b = dateOutlierBatch();
    $swap = dateOutlierInvoice($b->id, '82720', '2026-04-08');
    $ok1 = dateOutlierInvoice($b->id, '84386', '2026-08-14');
    $ok2 = dateOutlierInvoice($b->id, '84561', '2026-08-15');
    $ok3 = dateOutlierInvoice($b->id, '84731', '2026-08-16');
    $ok4 = dateOutlierInvoice($b->id, '84893', '2026-08-17');
    $bad = dateOutlierInvoice($b->id, '2670343', '2026-05-25');

    $pipeline = app(\App\Services\InvoicePipeline::class);
    (fn () => $this->flagDateOutliers($b))->call($pipeline);

    $swap->refresh(); $bad->refresh(); $ok1->refresh();
    expect($swap->invoice_date->format('Y-m-d'))->toBe('2026-08-04');
    expect((bool) $swap->needs_review)->toBeFalse();
    expect($swap->validation_notes)->toContain('تم تصحيح ترتيب اليوم والشهر');

    expect($bad->invoice_date->format('Y-m-d'))->toBe('2026-05-25');
    expect((bool) $bad->needs_review)->toBeTrue();
    expect($bad->validation_notes)->toContain('خارج شهر بقية الدفعة');

    expect($ok1->invoice_date->format('Y-m-d'))->toBe('2026-08-14');
    expect((bool) $ok1->needs_review)->toBeFalse();
});

// ---------------------------------------------------------------- repair command

it('invoices:repair-swapped-dates fixes existing rows and reports outliers, dry-run writes nothing', function () {
    $b = dateOutlierBatch();
    $swap = dateOutlierInvoice($b->id, '82720', '2026-04-08');
    foreach (['84386' => '2026-08-14', '84561' => '2026-08-15', '84731' => '2026-08-16', '84893' => '2026-08-17'] as $no => $d) {
        dateOutlierInvoice($b->id, $no, $d);
    }
    $bad = dateOutlierInvoice($b->id, '2670343', '2026-05-25');

    Artisan::call('invoices:repair-swapped-dates', ['--dry-run' => true, '--batch' => $b->id]);
    $out = Artisan::output();
    expect($out)->toContain('[dry-run]')->toContain('82720')->toContain('2026-04-08 → 2026-08-04')->toContain('2670343');
    expect($swap->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-04-08');

    Artisan::call('invoices:repair-swapped-dates', ['--batch' => $b->id]);
    expect($swap->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-08-04');
    expect($bad->refresh()->invoice_date->format('Y-m-d'))->toBe('2026-05-25'); // never guessed
    expect(Artisan::output())->toContain('swapped=1')->toContain('outliers=1');
});
