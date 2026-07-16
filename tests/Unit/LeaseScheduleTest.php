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
