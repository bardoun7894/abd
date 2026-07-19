<?php

use App\Services\LeaseScheduleGenerator;

beforeEach(function () {
    $this->gen = new LeaseScheduleGenerator();
});

/** A fully valid extracted lease contract row (fields the generator reads). */
function validLeaseContract(array $overrides = []): array
{
    return array_merge([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => 1000.0,
        'payment_frequency' => 'monthly',
    ], $overrides);
}

it('generates one row per payment_no in order starting at 1', function () {
    $rows = $this->gen->generate(validLeaseContract());

    expect($rows)->toHaveCount(12);
    expect(array_column($rows, 'payment_no'))->toBe(range(1, 12));
});

it('spaces monthly payments exactly one month apart starting on start_date', function () {
    $rows = $this->gen->generate(validLeaseContract());

    expect($rows[0]['due_date'])->toBe('2026-01-01');
    expect($rows[1]['due_date'])->toBe('2026-02-01');
    expect($rows[11]['due_date'])->toBe('2026-12-01');
});

it('spaces quarterly payments three months apart', function () {
    $rows = $this->gen->generate(validLeaseContract([
        'num_payments' => 4,
        'payment_value' => 3000.0,
        'payment_frequency' => 'quarterly',
    ]));

    expect(array_column($rows, 'due_date'))->toBe([
        '2026-01-01', '2026-04-01', '2026-07-01', '2026-10-01',
    ]);
});

it('uses payment_value directly for each installment when provided', function () {
    $rows = $this->gen->generate(validLeaseContract());

    foreach ($rows as $r) {
        expect((float) $r['amount'])->toBe(1000.0);
    }
});

it('falls back to rent_value / num_payments when payment_value is missing', function () {
    $rows = $this->gen->generate(validLeaseContract(['payment_value' => null]));

    foreach ($rows as $r) {
        expect((float) $r['amount'])->toBe(1000.0);
    }
});

it('absorbs the rounding remainder into the last payment so the total matches rent_value', function () {
    $rows = $this->gen->generate(validLeaseContract([
        'rent_value' => 1000.0,
        'num_payments' => 3,
        'payment_value' => null,
        'payment_frequency' => 'monthly',
    ]));

    $sum = round(array_sum(array_column($rows, 'amount')), 2);
    expect($sum)->toBe(1000.0);
    expect((float) $rows[0]['amount'])->toBe(333.33);
    expect((float) $rows[1]['amount'])->toBe(333.33);
    expect((float) $rows[2]['amount'])->toBe(333.34);
});

it('sets status to pending and remaining equal to the amount for every row', function () {
    $rows = $this->gen->generate(validLeaseContract());

    foreach ($rows as $r) {
        expect($r['status'])->toBe('pending');
        expect((float) $r['remaining'])->toBe((float) $r['amount']);
    }
});

it('generates a single payment due on start_date for a one-time lease', function () {
    $rows = $this->gen->generate(validLeaseContract([
        'num_payments' => 1,
        'payment_value' => 12000.0,
        'payment_frequency' => 'one-time',
    ]));

    expect($rows)->toHaveCount(1);
    expect($rows[0]['due_date'])->toBe('2026-01-01');
    expect((float) $rows[0]['amount'])->toBe(12000.0);
});

it('throws when start_date is missing', function () {
    expect(fn () => $this->gen->generate(validLeaseContract(['start_date' => null])))
        ->toThrow(InvalidArgumentException::class);
});

it('defaults num_payments to 1 when missing or zero', function () {
    $rows = $this->gen->generate(validLeaseContract(['num_payments' => 0, 'payment_value' => 5000.0]));

    expect($rows)->toHaveCount(1);
});

it('reconciles annual rent against contract total using duration', function () {
    // Real EJAR lease: 40,000 SAR/year × 5 years = 200,000 total.
    // 10 half-yearly payments × 20,000 = 200,000 — should be accepted verbatim.
    $result = $this->gen->generateWithWarnings(validLeaseContract([
        'start_date' => '2020-01-01',
        'end_date' => '2025-01-01',
        'duration' => '5 years',
        'rent_value' => 40000.0,
        'num_payments' => 10,
        'payment_value' => 20000.0,
        'payment_frequency' => 'semi-annual',
    ]));

    expect($result['warnings'])->toBeEmpty();
    expect(array_sum(array_column($result['rows'], 'amount')))->toBe(200000.0);
    expect($result['rows'])->toHaveCount(10);
});

it('falls back to even split when payment_value diverges from expected total', function () {
    $result = $this->gen->generateWithWarnings(validLeaseContract([
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => 2000.0, // AI hallucinated; should be 1,000.
        'payment_frequency' => 'monthly',
    ]));

    expect($result['warnings'])->not->toBeEmpty();
    expect(array_sum(array_column($result['rows'], 'amount')))->toBe(12000.0);
    foreach ($result['rows'] as $r) {
        expect((float) $r['amount'])->toBe(1000.0);
    }
});

it('absorbs rounding remainder into the last payment after reconciliation', function () {
    // 10,000 total split over 3 payments — last row should absorb the penny.
    $result = $this->gen->generateWithWarnings(validLeaseContract([
        'rent_value' => 10000.0,
        'num_payments' => 3,
        'payment_value' => null,
        'payment_frequency' => 'monthly',
    ]));

    expect((float) $result['rows'][0]['amount'])->toBe(3333.33);
    expect((float) $result['rows'][1]['amount'])->toBe(3333.33);
    expect((float) $result['rows'][2]['amount'])->toBe(3333.34);
    expect(array_sum(array_column($result['rows'], 'amount')))->toBe(10000.0);
});

it('clamps due dates that exceed end_date and records a warning', function () {
    $result = $this->gen->generateWithWarnings(validLeaseContract([
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-01',
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => 1000.0,
        'payment_frequency' => 'monthly',
    ]));

    expect($result['warnings'])->not->toBeEmpty();
    foreach ($result['rows'] as $r) {
        expect($r['due_date'] <= '2026-06-01')->toBeTrue();
    }
});

it('validateSchedule reports sum divergence', function () {
    $contract = validLeaseContract([
        'rent_value' => 12000.0,
        'num_payments' => 12,
        'payment_value' => null,
    ]);
    $badRows = $this->gen->generate($contract);
    $badRows[0]['amount'] = 999999.0;

    $errors = $this->gen->validateSchedule($badRows, $contract);

    expect($errors)->not->toBeEmpty();
    expect(implode(' ', $errors))->toContain('12000');
});

it('validateSchedule reports dates outside the lease term', function () {
    $contract = validLeaseContract([
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-01',
    ]);
    $badRows = $this->gen->generate($contract);
    $badRows[5]['due_date'] = '2027-01-01';

    $errors = $this->gen->validateSchedule($badRows, $contract);

    expect($errors)->not->toBeEmpty();
    expect(implode(' ', $errors))->toContain('2027-01-01');
});
