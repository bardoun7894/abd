<?php

use App\Console\Commands\StripAutoPostedPurchaseNotes;

uses(Tests\TestCase::class);

/**
 * The command edits financial records, so what it must NOT do matters as much as
 * what it does: the VAT breakdown has to survive byte-for-byte, and a note the
 * employee wrote must never be touched.
 */
function strip(string $note): string
{
    $m = new ReflectionMethod(StripAutoPostedPurchaseNotes::class, 'strip');
    $m->setAccessible(true);

    return $m->invoke(new StripAutoPostedPurchaseNotes(), $note);
}

it('drops the machine tail and keeps the VAT breakdown verbatim', function () {
    $before = 'قبل الضريبة: 78.26 | ضريبة: 11.74 | مُرحّل آلياً من استخراج الفواتير (دفعة #15 صفحة 26)';

    expect(strip($before))->toBe('قبل الضريبة: 78.26 | ضريبة: 11.74');
});

it('returns an empty string when the note was ONLY the machine tail', function () {
    // The caller turns '' into NULL, matching a purchase entered with no note.
    $before = 'مُرحّل آلياً من استخراج الفواتير (دفعة #3 صفحة 1)';

    expect(strip($before))->toBe('');
});

it('keeps a note the employee typed completely untouched', function () {
    $human = 'المورد سلّم البضاعة ناقصة، رجاء المتابعة';

    expect(strip($human))->toBe($human);
});

it('preserves a human note that sits alongside the machine tail', function () {
    $before = 'قبل الضريبة: 10 | ملاحظة الموظف: مرتجع | مُرحّل آلياً من استخراج الفواتير (دفعة #1 صفحة 2)';

    expect(strip($before))->toBe('قبل الضريبة: 10 | ملاحظة الموظف: مرتجع');
});

it('is idempotent — a second pass changes nothing', function () {
    $once = strip('قبل الضريبة: 78.26 | ضريبة: 11.74 | مُرحّل آلياً من استخراج الفواتير (دفعة #15 صفحة 26)');

    expect(strip($once))->toBe($once);
});

it('no longer writes the machine tail when mapping a new invoice', function () {
    // Guards the source of the problem, not just the cleanup of its output.
    $src = file_get_contents(base_path('app/Services/InvoicePurchaseMapper.php'));
    $codeLines = array_filter(
        explode("\n", $src),
        fn ($line) => ! str_starts_with(ltrim($line), '*') && ! str_starts_with(ltrim($line), '//')
    );

    expect(implode("\n", $codeLines))->not->toContain("noteParts[] = 'مُرحّل آلياً");
});
