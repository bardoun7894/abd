<?php

use App\Services\LeaseExtractionService;
use App\Services\LeaseScheduleGenerator;

uses(Tests\TestCase::class);

/**
 * Regression tests for the two extraction bugs a real official Saudi «إيجار»
 * contract exposed (RK08 / 20754898102, reported from noor-alsabah):
 *
 *  1. WRONG START DATE — page 1 prints «تاريخ إبرام العقد» (2026-05-20) right
 *     beside «تاريخ بداية مدة الإيجار» (2026-08-12). The prompt asked for
 *     "تاريخ بداية العقد", which matches the SEALING label more closely, so the
 *     model returned the sealing date — three months early.
 *
 *  2. VAT DROPPED FROM THE INSTALLMENTS — the same page prints annual rent
 *     55,000 (pre-VAT), VAT 8,250, and total 63,250 (VAT-inclusive). A single
 *     `rent_value` field gave no way to disambiguate, so the model sometimes
 *     returned 55,000; the generator then split that into 2 × 27,500 and the
 *     tenant was billed 8,250 short across the contract.
 *
 * The structural fix is that the contract PRINTS its own schedule (جدول سداد
 * الدفعات) with real due dates and VAT-inclusive totals, and the app now uses
 * that table verbatim instead of deriving one.
 *
 * Numbers below are transcribed from the actual PDF — do not "tidy" them.
 */

/** The contract's payment table, exactly as printed in section 13. */
function ijarPrintedPayments(): array
{
    return [
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'rent_value' => 27500.00, 'vat' => 4125.00, 'total' => 31625.00],
        ['payment_no' => 2, 'due_date' => '2027-02-22', 'rent_value' => 27500.00, 'vat' => 4125.00, 'total' => 31625.00],
    ];
}

/** The contract as the fixed extraction should return it. */
function ijarContract(array $overrides = []): array
{
    return array_merge([
        'contract_no' => '20754898102 / 1-0',
        'start_date' => '2026-08-12',   // tenancy start, NOT the 2026-05-20 sealing date
        'end_date' => '2027-08-11',
        'rent_value' => 63250.00,       // VAT-inclusive grand total
        'annual_rent' => 55000.00,
        'vat_amount' => 8250.00,
        'num_payments' => 2,
        'payment_value' => 31625.00,
        'payment_frequency' => 'نصف سنوي',
        'payments' => ijarPrintedPayments(),
    ], $overrides);
}

// ---------------------------------------------------------------------------
// The prompt must make both traps impossible to fall into
// ---------------------------------------------------------------------------

it('tells the model to take the tenancy start date, never the contract sealing date', function () {
    $prompt = file_get_contents(base_path('resources/prompts/lease-extraction.md'));

    expect($prompt)->toContain('تاريخ بداية مدة الإيجار');
    expect($prompt)->toContain('Tenancy Start Date');
    // The trap must be named explicitly, not just implied.
    expect($prompt)->toContain('تاريخ إبرام العقد');
    expect($prompt)->toContain('Contract Sealing Date');

    // The old wording is what caused the bug — it must be gone.
    expect($prompt)->not->toContain('AR: تاريخ البداية، تاريخ بداية العقد');
});

it('tells the model that rent_value includes VAT, and splits out the parts', function () {
    $prompt = file_get_contents(base_path('resources/prompts/lease-extraction.md'));

    expect($prompt)->toContain('INCLUDING VAT');
    expect($prompt)->toContain('annual_rent');
    expect($prompt)->toContain('vat_amount');
    // The worked example pins the exact figures from the real contract.
    expect($prompt)->toContain('55000 + VAT 8250 → rent_value = 63250');
});

it('asks for the schedule the contract already prints', function () {
    $prompt = file_get_contents(base_path('resources/prompts/lease-extraction.md'));

    expect($prompt)->toContain('جدول سداد الدفعات');
    expect($prompt)->toContain('payment_no');
    expect($prompt)->toContain('due_date');
    // Hijri columns sit beside the Gregorian ones and must not be mixed in.
    expect($prompt)->toContain('Ignore the Hijri');
});

// ---------------------------------------------------------------------------
// The service must carry the new fields through
// ---------------------------------------------------------------------------

it('declares annual_rent and vat_amount as extracted fields', function () {
    expect(LeaseExtractionService::FIELDS)->toContain('annual_rent');
    expect(LeaseExtractionService::FIELDS)->toContain('vat_amount');
    expect(LeaseExtractionService::FIELDS)->toContain('rent_value');
});

it('normalizes the printed schedule rows', function () {
    $svc = new LeaseExtractionService();
    $rows = $svc->normalizePayments(ijarPrintedPayments());

    expect($rows)->toHaveCount(2);
    expect($rows[0]['due_date'])->toBe('2026-08-22');
    expect($rows[0]['total'])->toBe(31625.00);
    expect($rows[1]['due_date'])->toBe('2027-02-22');
    expect(array_sum(array_column($rows, 'total')))->toBe(63250.00);
});

it('reconstructs a row total from rent + VAT when the total column is unreadable', function () {
    $rows = (new LeaseExtractionService())->normalizePayments([
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'rent_value' => 27500.00, 'vat' => 4125.00, 'total' => null],
    ]);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['total'])->toBe(31625.00);
});

it('drops rows it cannot read rather than guessing them', function () {
    $rows = (new LeaseExtractionService())->normalizePayments([
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'total' => 31625.00],
        ['payment_no' => 2, 'due_date' => null, 'total' => 31625.00],          // no date
        ['payment_no' => 3, 'due_date' => '2027-02-22', 'total' => 0],          // no amount
        'not an array',
    ]);

    expect($rows)->toHaveCount(1);
    expect($rows[0]['due_date'])->toBe('2026-08-22');
});

it('orders the schedule by payment_no even if the table came back shuffled', function () {
    $rows = (new LeaseExtractionService())->normalizePayments([
        ['payment_no' => 2, 'due_date' => '2027-02-22', 'total' => 31625.00],
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'total' => 31625.00],
    ]);

    expect(array_column($rows, 'payment_no'))->toBe([1, 2]);
});

it('returns an empty schedule for contracts that print no table', function () {
    $svc = new LeaseExtractionService();

    expect($svc->normalizePayments(null))->toBe([]);
    expect($svc->normalizePayments([]))->toBe([]);
    expect($svc->normalizePayments('nonsense'))->toBe([]);
});

// ---------------------------------------------------------------------------
// The generator must USE the printed table, and still work without one
// ---------------------------------------------------------------------------

it('builds the schedule from the printed table, VAT included, on the real due dates', function () {
    $result = (new LeaseScheduleGenerator())->generateWithWarnings(ijarContract());
    $rows = $result['rows'];

    expect($rows)->toHaveCount(2);

    // The bug: these were 27,500 each (VAT stripped). They must be the printed totals.
    expect($rows[0]['amount'])->toBe(31625.00);
    expect($rows[1]['amount'])->toBe(31625.00);
    expect(array_sum(array_column($rows, 'amount')))->toBe(63250.00);

    // The bug: due dates were derived from start_date (08-12 and 02-12).
    // The contract actually states the 22nd of each month.
    expect($rows[0]['due_date'])->toBe('2026-08-22');
    expect($rows[1]['due_date'])->toBe('2027-02-22');

    expect($result['warnings'])->toBe([]);
});

it('falls back to deriving a schedule when no table was printed', function () {
    $contract = ijarContract(['payments' => []]);
    $rows = (new LeaseScheduleGenerator())->generate($contract);

    // Still 2 payments totalling the VAT-inclusive contract value — the derived
    // path is now correct too, because rent_value itself is VAT-inclusive.
    expect($rows)->toHaveCount(2);
    expect(array_sum(array_column($rows, 'amount')))->toBe(63250.00);
});

it('warns when the printed table disagrees with the stated payment count', function () {
    $contract = ijarContract(['num_payments' => 4]);
    $result = (new LeaseScheduleGenerator())->generateWithWarnings($contract);

    expect($result['rows'])->toHaveCount(2);   // the printed table wins
    expect($result['warnings'])->not->toBeEmpty();
    expect($result['warnings'][0])->toContain('عدد الدفعات');
});

it('warns when the printed table does not sum to the contract total', function () {
    $contract = ijarContract([
        'payments' => [
            ['payment_no' => 1, 'due_date' => '2026-08-22', 'total' => 10000.00],
        ],
    ]);
    $result = (new LeaseScheduleGenerator())->generateWithWarnings($contract);

    expect($result['rows'])->toHaveCount(1);
    expect(implode(' ', $result['warnings']))->toContain('لا يطابق إجمالي قيمة العقد');
});

it('passes its own schedule validation for the real contract', function () {
    $gen = new LeaseScheduleGenerator();
    $contract = ijarContract();
    $rows = $gen->generateWithWarnings($contract)['rows'];

    // The approve endpoint refuses to save when this returns anything.
    expect($gen->validateSchedule($rows, $contract))->toBe([]);
});

it('still generates from a printed table when start_date is missing', function () {
    // The derived path throws without start_date; the printed table does not
    // need it, so a contract with a schedule must not be blocked by that.
    $contract = ijarContract();
    unset($contract['start_date']);

    $rows = (new LeaseScheduleGenerator())->generate($contract);
    expect($rows)->toHaveCount(2);
    expect($rows[0]['due_date'])->toBe('2026-08-22');
});

// ---------------------------------------------------------------------------
// A SECOND real contract: 5 years, 10 payments, ZERO VAT (20871952286-2)
// ---------------------------------------------------------------------------

/**
 * Found while verifying the fix against a second real «إيجار» PDF. It exposed a
 * bug in expectedTotal(): it scaled rent_value by the lease length, which was
 * right when rent_value meant the ANNUAL rent but wrong now that extraction
 * returns the CONTRACT TOTAL. A 5-year lease of 200,000 was scored against
 * 1,000,000, so every schedule "failed" reconciliation — a false warning on the
 * shop path and a BLOCKED SAVE on the leases path, where validateSchedule()
 * treats the mismatch as an error.
 *
 * Also proves zero-VAT contracts still work: not every «إيجار» charges VAT.
 */
function ijar5yContract(array $overrides = []): array
{
    $payments = [];
    $due = ['2024-01-11', '2024-07-11', '2025-01-11', '2025-07-11', '2026-01-11',
            '2026-07-11', '2027-01-11', '2027-07-11', '2028-01-11', '2028-07-11'];
    foreach ($due as $i => $d) {
        $payments[] = [
            'payment_no' => $i + 1,
            'due_date' => $d,
            'rent_value' => 20000.00,
            'vat' => 0.00,
            'total' => 20000.00,
        ];
    }

    return array_merge([
        'start_date' => '2024-01-01',   // tenancy start (sealing was 2023-12-27)
        'end_date' => '2028-12-31',
        'rent_value' => 200000.00,      // whole-contract total
        'annual_rent' => 40000.00,
        'vat_amount' => 0.00,
        'num_payments' => 10,
        'payment_value' => 20000.00,
        'payments' => $payments,
    ], $overrides);
}

it('does not inflate the expected total on a multi-year contract', function () {
    $result = (new LeaseScheduleGenerator())->generateWithWarnings(ijar5yContract());

    expect($result['rows'])->toHaveCount(10);
    expect(array_sum(array_column($result['rows'], 'amount')))->toBe(200000.00);

    // The bug: "مجموع جدول السداد المطبوع (200000) لا يطابق إجمالي قيمة العقد (1000000)".
    expect($result['warnings'])->toBe([]);
});

it('lets a multi-year contract pass schedule validation, so the save is not blocked', function () {
    $gen = new LeaseScheduleGenerator();
    $contract = ijar5yContract();

    // validateSchedule() failing here is what blocks approve() with a 422.
    expect($gen->validateSchedule($gen->generateWithWarnings($contract)['rows'], $contract))->toBe([]);
});

it('handles a zero-VAT contract without inventing tax', function () {
    $rows = (new LeaseScheduleGenerator())->generate(ijar5yContract());

    expect($rows[0]['amount'])->toBe(20000.00);
    expect($rows[0]['due_date'])->toBe('2024-01-11');
    expect($rows[9]['due_date'])->toBe('2028-07-11');
});

it('still scales a genuinely ANNUAL rent_value over the lease term', function () {
    // Legacy/hand-entered rows hold one year's rent with no annual_rent field
    // and no printed table. That reading must keep working.
    $gen = new LeaseScheduleGenerator();
    $rows = $gen->generateWithWarnings([
        'start_date' => '2024-01-01',
        'end_date' => '2025-12-31',   // 2 years
        'rent_value' => 40000.00,     // per YEAR
        'num_payments' => 2,
        'payment_value' => 40000.00,  // 2 × 40,000 = 80,000 total
    ]);

    expect($rows['rows'])->toHaveCount(2);
    expect($rows['warnings'])->toBe([]);
});
