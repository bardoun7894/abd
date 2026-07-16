<?php

use App\Console\Commands\LeaseScanAlerts;

it('computes signed days-until a future date as positive', function () {
    expect(LeaseScanAlerts::daysUntil('2026-07-15', '2026-07-25'))->toBe(10);
});

it('computes signed days-until a past date as negative (overdue)', function () {
    expect(LeaseScanAlerts::daysUntil('2026-07-15', '2026-07-10'))->toBe(-5);
});

it('computes zero for the due day itself', function () {
    expect(LeaseScanAlerts::daysUntil('2026-07-15', '2026-07-15'))->toBe(0);
});

it('matches the payment window for exactly 10, 5, or 0 days out', function () {
    expect(LeaseScanAlerts::matchedPaymentWindow(10))->toBe('10');
    expect(LeaseScanAlerts::matchedPaymentWindow(5))->toBe('5');
    expect(LeaseScanAlerts::matchedPaymentWindow(0))->toBe('0');
});

it('matches the payment window as overdue for any negative days-until', function () {
    expect(LeaseScanAlerts::matchedPaymentWindow(-1))->toBe('overdue');
    expect(LeaseScanAlerts::matchedPaymentWindow(-30))->toBe('overdue');
});

it('does not match a payment window for days not in {10,5,0} or negative', function () {
    expect(LeaseScanAlerts::matchedPaymentWindow(9))->toBeNull();
    expect(LeaseScanAlerts::matchedPaymentWindow(1))->toBeNull();
    expect(LeaseScanAlerts::matchedPaymentWindow(20))->toBeNull();
});

it('matches the contract window for exactly 30, 15, or 0 days out', function () {
    expect(LeaseScanAlerts::matchedContractWindow(30))->toBe('30');
    expect(LeaseScanAlerts::matchedContractWindow(15))->toBe('15');
    expect(LeaseScanAlerts::matchedContractWindow(0))->toBe('0');
});

it('does not match a contract window for any other day count, including negative', function () {
    expect(LeaseScanAlerts::matchedContractWindow(29))->toBeNull();
    expect(LeaseScanAlerts::matchedContractWindow(-1))->toBeNull();
    expect(LeaseScanAlerts::matchedContractWindow(1))->toBeNull();
});

it('builds a dedup key unique per payment + window so each window fires once', function () {
    expect(LeaseScanAlerts::paymentDedupKey(42, '10'))->toBe('lease_payment:42:10');
    expect(LeaseScanAlerts::paymentDedupKey(42, 'overdue'))->toBe('lease_payment:42:overdue');
    expect(LeaseScanAlerts::paymentDedupKey(42, '10'))->not->toBe(LeaseScanAlerts::paymentDedupKey(42, '5'));
});

it('builds a dedup key unique per contract + window so each window fires once', function () {
    expect(LeaseScanAlerts::contractDedupKey(7, '30'))->toBe('lease_contract:7:30');
    expect(LeaseScanAlerts::contractDedupKey(7, '30'))->not->toBe(LeaseScanAlerts::contractDedupKey(8, '30'));
});
