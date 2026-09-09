<?php

use App\Services\InvoiceExtractionService;
use Carbon\Carbon;

/** Call the private parseDate() directly. */
function parseDateVia(?string $input): ?string
{
    $svc = new InvoiceExtractionService();
    $ref = new ReflectionMethod($svc, 'parseDate');
    $ref->setAccessible(true);

    return $ref->invoke($svc, $input);
}

beforeEach(fn () => Carbon::setTestNow('2026-07-24'));
afterEach(fn () => Carbon::setTestNow());

it('keeps an ISO date as-is', function () {
    expect(parseDateVia('2026-07-11'))->toBe('2026-07-11');
});

it('parses Saudi DD/MM/YYYY day-first, not US MM/DD', function () {
    expect(parseDateVia('11/07/2026'))->toBe('2026-07-11'); // 11 July, NOT 7 Nov
    expect(parseDateVia('11-07-2026'))->toBe('2026-07-11');
    expect(parseDateVia('11.07.2026'))->toBe('2026-07-11');
});

it('flips an ambiguous pair to avoid a FALSE future date (the video bug)', function () {
    // "07-11-2026": DD/MM = 7 Nov 2026 (future vs today 24 Jul) -> swap to 11 Jul 2026.
    expect(parseDateVia('07-11-2026'))->toBe('2026-07-11');
});

it('treats day>12 as unambiguous DD/MM', function () {
    expect(parseDateVia('25/12/2025'))->toBe('2025-12-25');
});

it('expands a 2-digit year and returns null for junk', function () {
    expect(parseDateVia('05/03/25'))->toBe('2025-03-05');
    expect(parseDateVia('—'))->toBeNull();
});

// Client report 2026-09-09: invoices printed 04-08-2026 were stored as 2026-04-08
// (April) and vanished from the August search. The model was returning ISO with
// the month swapped because the prompt's own examples taught US MM/DD; the prompt
// now asks for the date verbatim, so parseDate() must own every printed form.
it('parses the printed forms Saudi suppliers actually use, day-first', function () {
    expect(parseDateVia('04-08-2026'))->toBe('2026-08-04');   // Al-Borozah, 4 Aug
    expect(parseDateVia('25-08-2026'))->toBe('2026-08-25');
    expect(parseDateVia('15-May-26'))->toBe('2026-05-15');    // Nahla
    expect(parseDateVia('15-Aug-26'))->toBe('2026-08-15');
    expect(parseDateVia('24-08-2026 8:49:20PM'))->toBe('2026-08-24'); // Caesar, time appended
    expect(parseDateVia('2026/08/04'))->toBe('2026-08-04');   // Y/M/D with slashes
    expect(parseDateVia('٢٥-٠٨-٢٠٢٦'))->toBe('2026-08-25');   // Arabic-Indic digits
});

it('keeps the printed date text in invoice_date_raw, not the parsed ISO', function () {
    $out = (new InvoiceExtractionService())->normalize(['invoice_date' => '04-08-2026']);
    expect($out['invoice_date'])->toBe('2026-08-04');
    expect($out['invoice_date_raw'])->toBe('04-08-2026');
});

it('the prompt never demonstrates US month-first and asks for the date as printed', function () {
    $p = file_get_contents(__DIR__.'/../../resources/prompts/invoice-extraction.md');
    expect($p)->not->toContain('05/15/2026')
        ->not->toContain('"invoice_date":"2026-04-10"')
        ->not->toContain('"invoice_date":"2026-05-15"');
    expect($p)->toContain('DD/MM/YYYY');
    expect($p)->toContain('"invoice_date":"04/10/2026"');
});
