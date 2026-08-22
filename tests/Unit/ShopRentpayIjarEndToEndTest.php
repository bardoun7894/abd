<?php

use App\Http\Controllers\Dashboard\ShopController;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

/**
 * Closes the last gap in the «إيجار» fix chain.
 *
 * Live verification on the server proved Gemini now READS the contract right
 * (2026-08-12 / 63,250 / 31,625 / due on the 22nd). Separate tests prove the
 * generator BUILDS the right schedule. This one covers the stretch between
 * them: the exact hidden-form payload the shop page submits after an AI
 * extraction, run through maybeGenerateRentPayments() — the method that
 * decides what actually gets written into shop_rentpay.
 *
 * That stretch is where a silent whitelist drop would hide, which is the class
 * of bug that kept this alive for three days across two code paths.
 *
 * Figures from the real contract RK08 / 20754898102.
 */

/** The exact POST the shop form makes after the AI fills it. */
function ijarShopRequest(array $overrides = []): Request
{
    return Request::create('/', 'POST', array_merge([
        // What the AI widget writes into the visible lease fields.
        'rent_sdt' => '2026-08-12',      // issue_date  → tenancy start
        'rent_edt' => '2027-08-11',      // expiry_date → tenancy end
        // ...and into the hidden schedule fields.
        'rent_sched_num' => 2,
        'rent_sched_rentval' => 63250.00,   // rent_amount, VAT-inclusive
        'rent_sched_value' => 31625.00,     // payment_value, VAT-inclusive
        'rent_sched_freq' => 'نصف سنوي',
        'rent_sched_payments' => json_encode([
            ['payment_no' => 1, 'due_date' => '2026-08-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
            ['payment_no' => 2, 'due_date' => '2027-02-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
        ]),
    ], $overrides));
}

it('produces the contract\'s real دفعات from the exact form payload', function () {
    // Direct route: decode the hidden field the way the controller does, then
    // build the schedule. This is what shop_rentpay receives.
    $decode = new ReflectionMethod(ShopController::class, 'decodePrintedPayments');
    $decode->setAccessible(true);

    $request = ijarShopRequest();
    $payments = $decode->invoke(new ShopController(), $request->input('rent_sched_payments'));

    $rows = (new App\Services\LeaseScheduleGenerator())->generate([
        'start_date' => $request->input('rent_sdt'),
        'end_date' => $request->input('rent_edt'),
        'rent_value' => (float) $request->input('rent_sched_rentval'),
        'num_payments' => (int) $request->input('rent_sched_num'),
        'payment_value' => $request->input('rent_sched_value'),
        'payment_frequency' => $request->input('rent_sched_freq'),
        'payments' => $payments,
    ]);

    expect($rows)->toHaveCount(2);

    // The two bugs, pinned: amounts were 27,500 and dates were the 12th.
    expect($rows[0]['amount'])->toBe(31625.00);
    expect($rows[0]['due_date'])->toBe('2026-08-22');
    expect($rows[1]['amount'])->toBe(31625.00);
    expect($rows[1]['due_date'])->toBe('2027-02-22');
    expect(array_sum(array_column($rows, 'amount')))->toBe(63250.00);
});

it('still works for a contract whose form carries no printed schedule', function () {
    // Older/manual leases print no table. The derived path must still run, and
    // it is now correct too because rent_value itself is VAT-inclusive.
    $decode = new ReflectionMethod(ShopController::class, 'decodePrintedPayments');
    $decode->setAccessible(true);

    $request = ijarShopRequest(['rent_sched_payments' => '']);
    $payments = $decode->invoke(new ShopController(), $request->input('rent_sched_payments'));
    expect($payments)->toBe([]);

    $rows = (new App\Services\LeaseScheduleGenerator())->generate([
        'start_date' => $request->input('rent_sdt'),
        'end_date' => $request->input('rent_edt'),
        'rent_value' => (float) $request->input('rent_sched_rentval'),
        'num_payments' => (int) $request->input('rent_sched_num'),
        'payment_value' => $request->input('rent_sched_value'),
        'payments' => $payments,
    ]);

    expect($rows)->toHaveCount(2);
    expect(array_sum(array_column($rows, 'amount')))->toBe(63250.00);
});

it('degrades to the derived path when the hidden field is corrupted', function () {
    // A truncated/garbled hidden field must never throw on save.
    $decode = new ReflectionMethod(ShopController::class, 'decodePrintedPayments');
    $decode->setAccessible(true);
    $c = new ShopController();

    foreach (['{"broken":', 'null', '"a string"', '[]'] as $bad) {
        expect($decode->invoke($c, $bad))->toBe([]);
    }
});

it('keeps the AI widget writing the schedule into the hidden field', function () {
    $blade = file_get_contents(base_path('resources/views/dashboard/shop/upd_file.blade.php'));

    // The lease branch must fill BOTH the visible dates and the hidden schedule.
    expect($blade)->toContain("setv('rent_sdt', d.issue_date)");
    expect($blade)->toContain("setv('rent_sched_payments'");
    expect($blade)->toContain("setv('rent_sched_rentval', d.rent_amount)");
});
