<?php

uses(Tests\TestCase::class);

use App\Http\Controllers\Dashboard\InvoiceController;

/**
 * Production incident (شركة صباح النور, batch 12, 2026-08-09): every row in the
 * results grid showed "—" under المرفق and no PDF could be opened.
 *
 * Root cause: the host disables exec(), so poppler never rasterizes the pages and
 * InvoicePipeline::wholeDocument() falls back to storing
 * "uploads/invoices/pages/batch_N/source.pdf#page=K" as image_path. imageUrl()
 * anchored its extension check at the end of the WHOLE string, so the "#page=K"
 * fragment made every fallback path fail the match and return null.
 */
function invImageUrl(int $batchId, ?string $path): ?string
{
    return (new ReflectionMethod(InvoiceController::class, 'imageUrl'))
        ->invoke(app(InvoiceController::class), $batchId, $path);
}

it('builds a URL for a whole-document fallback path carrying a #page fragment', function () {
    $url = invImageUrl(12, 'uploads/invoices/pages/batch_12/source.pdf#page=64');

    expect($url)->not->toBeNull();
    expect($url)->toContain('/dashboard/invoices/12/file/source.pdf');
    expect($url)->toEndWith('#page=64');
});

it('builds a URL for a rasterized page image', function () {
    $url = invImageUrl(7, 'uploads/invoices/pages/batch_7/page-3.png');

    expect($url)->toEndWith('/dashboard/invoices/7/file/page-3.png');
});

it('builds a URL for an FPDI sub-PDF page with no fragment', function () {
    $url = invImageUrl(7, 'uploads/invoices/pages/batch_7/page-2.pdf');

    expect($url)->toEndWith('/dashboard/invoices/7/file/page-2.pdf');
});

it('returns null for a missing or non-document path', function () {
    expect(invImageUrl(1, null))->toBeNull();
    expect(invImageUrl(1, ''))->toBeNull();
    expect(invImageUrl(1, 'uploads/invoices/pages/batch_1/notes.txt'))->toBeNull();
    expect(invImageUrl(1, 'uploads/invoices/pages/batch_1/source.exe#page=1'))->toBeNull();
});

it('never lets a fragment smuggle a path traversal into the served file name', function () {
    $url = invImageUrl(3, 'uploads/invoices/pages/batch_3/../../../.env.pdf#page=1');

    expect($url)->toContain('/file/.env.pdf');
    expect($url)->not->toContain('..');
});

/*
 * The review / fix screens used to hard-code <img src="{{ image_url }}">. Once
 * imageUrl() started returning PDF URLs (above), that would have swapped one silent
 * failure for another — a broken-image icon. The shared partial must embed those.
 */
it('embeds a PDF attachment in a viewer instead of an <img> on the review screens', function () {
    $html = view('dashboard.invoices._attachment', [
        'url' => 'http://localhost/dashboard/invoices/12/file/source.pdf#page=64',
    ])->render();

    expect($html)->toContain('<iframe');
    expect($html)->toContain('#page=64');
    expect($html)->not->toContain('<img');
});

it('still renders a rasterized page as a zoomable image', function () {
    $html = view('dashboard.invoices._attachment', [
        'url' => 'http://localhost/dashboard/invoices/7/file/page-3.png',
    ])->render();

    expect($html)->toContain('<img');
    expect($html)->toContain('inv-thumb');
    expect($html)->not->toContain('<iframe');
});

it('keeps the "no page image" placeholder when there is no attachment', function () {
    $html = view('dashboard.invoices._attachment', ['url' => null])->render();

    expect($html)->toContain('لا توجد صورة للصفحة');
});

it('wires the review and error screens to the shared attachment partial', function () {
    foreach (['review', 'error'] as $screen) {
        $src = file_get_contents(base_path("resources/views/dashboard/invoices/{$screen}.blade.php"));
        expect($src)->toContain("@include('dashboard.invoices._attachment'");
        expect($src)->not->toContain('<img src="{{ $invoice->image_url }}"');
    }
});

it('serves every extension imageUrl() hands out with a real content type', function () {
    $mimes = (new ReflectionClass(InvoiceController::class))->getConstant('MIME_BY_EXT');

    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif', 'pdf'] as $ext) {
        expect($mimes)->toHaveKey($ext);
    }
    expect($mimes['pdf'])->toBe('application/pdf');
});
