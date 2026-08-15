<?php

uses(Tests\TestCase::class);

use App\Services\InvoicePurchaseMapper;
use App\Services\PdfPageSplitter;
use Illuminate\Support\Facades\File;

/**
 * Client report (2026-08-14, sabah): «أي فاتورة أضغط يجي بس فاتورة رقم واحد» —
 * opening ANY pushed purchase's attachment showed the FIRST invoice of the batch.
 *
 * Root cause: on hosts without poppler the pipeline stores
 * "…/batch_N/source.pdf#page=K" as image_path. copyImageToPurchases() stripped the
 * "#page=K" fragment and copied the WHOLE multi-invoice source.pdf, so every
 * purchase attachment opened at page 1. The copy must extract just page K with
 * FPDI (pure PHP — exec() is disabled on the prod host).
 */
function makeSourcePdf(string $path, int $pages): void
{
    File::ensureDirectoryExists(dirname($path));
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    for ($n = 1; $n <= $pages; $n++) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 40);
        $pdf->Text(20, 20, "INVOICE PAGE {$n}");
    }
    $pdf->Output($path, 'F');
}

beforeEach(function () {
    $this->batchDir = public_path('uploads/invoices/pages/batch_999');
    $this->srcRel = 'uploads/invoices/pages/batch_999/source.pdf';
    makeSourcePdf(public_path($this->srcRel), 3);
});

afterEach(function () {
    File::deleteDirectory(public_path('uploads/invoices/pages/batch_999'));
    // Remove any inv_* copies this test wrote into the purchases dir.
    foreach (glob(public_path('uploads/users/images/inv_*.pdf')) as $f) {
        if (filemtime($f) > time() - 60) {
            @unlink($f);
        }
    }
});

it('copies ONLY the fragment page when image_path carries #page=K', function () {
    $out = InvoicePurchaseMapper::copyImageToPurchases($this->srcRel.'#page=2');

    expect($out)->not->toBe($this->srcRel.'#page=2'); // did not fail-open
    expect($out)->toStartWith('uploads/users/images/inv_');
    expect($out)->toEndWith('.pdf');
    expect($out)->not->toContain('#');

    $abs = public_path($out);
    expect(is_file($abs))->toBeTrue();
    // A single-page PDF — not the 3-page source.
    expect((new PdfPageSplitter())->pageCount($abs))->toBe(1);
});

it('extracts the LAST page correctly too', function () {
    $out = InvoicePurchaseMapper::copyImageToPurchases($this->srcRel.'#page=3');

    expect((new PdfPageSplitter())->pageCount(public_path($out)))->toBe(1);
});

it('still copies a fragment-free page PDF wholesale', function () {
    $out = InvoicePurchaseMapper::copyImageToPurchases('uploads/invoices/pages/batch_999/page-1.pdf');
    // page-1.pdf doesn't exist -> fail-open returns original path
    expect($out)->toBe('uploads/invoices/pages/batch_999/page-1.pdf');

    File::copy(public_path($this->srcRel), $this->batchDir.'/page-1.pdf');
    $out = InvoicePurchaseMapper::copyImageToPurchases('uploads/invoices/pages/batch_999/page-1.pdf');
    expect($out)->toStartWith('uploads/users/images/inv_');
    expect(is_file(public_path($out)))->toBeTrue();
});

it('fails open when the fragment page is out of range', function () {
    $out = InvoicePurchaseMapper::copyImageToPurchases($this->srcRel.'#page=99');

    // Fail-open contract: keep SOMETHING usable rather than losing the attachment.
    expect($out)->not->toBeNull();
});
