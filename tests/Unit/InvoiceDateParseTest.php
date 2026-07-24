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
