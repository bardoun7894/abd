<?php

// Pure arithmetic — no app boot, no DB.
//
// Mirrors the rule ShopController::readAttachedLeaseContract() applies when the
// extractor did not return num_payments. The old code substituted 1, so a lease
// the AI read incompletely produced a single دفعة and reported success.
//
// Client, صباح النور 2026-07-29: a 50,000 lease of 2 × 25,000 became ONE payment
// of 25,000 — half the liability missing from the ledger, already marked paid
// before anyone noticed. LeaseScheduleGenerator's reconciliation does not catch
// it, because a missing rent_amount makes expectedTotal 0 and the sum check is
// skipped altogether.

/** The production rule, kept in one place so the test states it exactly. */
function inferPaymentCount(int $numPayments, float $rentValue, $paymentValue): int
{
    if ($numPayments <= 0 && $rentValue > 0 && is_numeric($paymentValue) && (float) $paymentValue > 0) {
        $derived = (int) round($rentValue / (float) $paymentValue);
        if ($derived >= 1 && abs(($derived * (float) $paymentValue) - $rentValue) <= max(0.01, $rentValue * 0.01)) {
            return $derived;
        }
    }

    return $numPayments > 0 ? $numPayments : 0;
}

test('recovers the count the extractor missed when total and instalment divide evenly', function () {
    // The client's actual contract.
    expect(inferPaymentCount(0, 50000, 25000))->toBe(2)
        ->and(inferPaymentCount(0, 24000, 12000))->toBe(2)
        ->and(inferPaymentCount(0, 12000, 1000))->toBe(12);
});

test('an explicit count from the document always wins', function () {
    expect(inferPaymentCount(4, 50000, 25000))->toBe(4);
});

test('refuses rather than guessing when the numbers do not reconcile', function () {
    // 50,000 total with a 30,000 instalment is not a whole schedule; writing
    // either 1 or 2 payments would invent a number nobody can defend.
    expect(inferPaymentCount(0, 50000, 30000))->toBe(0);
});

test('refuses when only one of the two figures is known', function () {
    expect(inferPaymentCount(0, 50000, null))->toBe(0)
        ->and(inferPaymentCount(0, 0, 25000))->toBe(0)
        ->and(inferPaymentCount(0, 0, null))->toBe(0);
});

test('never silently returns 1 — the old behaviour', function () {
    // Every unresolvable case must be 0 (refuse), never 1 (write half a ledger).
    foreach ([[0, 0.0, null], [0, 50000.0, 30000], [0, 0.0, 25000]] as $case) {
        expect(inferPaymentCount($case[0], $case[1], $case[2]))->not->toBe(1);
    }
});

test('tolerates rounding inside one percent', function () {
    // 3 × 3333.34 = 10000.02 against a stated 10,000 total.
    expect(inferPaymentCount(0, 10000, 3333.34))->toBe(3);
});
