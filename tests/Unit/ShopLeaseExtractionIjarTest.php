<?php

use App\Http\Controllers\Dashboard\ShopController;
use App\Services\ShopAiExtractor;

uses(Tests\TestCase::class);

/**
 * The SECOND lease-extraction path.
 *
 * On 2026-08-19 the «إيجار» date/VAT bugs were fixed in LeaseExtractionService +
 * resources/prompts/lease-extraction.md — the «الإيجارات» module. But the client
 * uploads contracts from «إدارة المحل» (shop file → تحميل المستندات), which runs
 * a COMPLETELY SEPARATE extractor: ShopAiExtractor, with its own inline prompt.
 * That prompt was untouched, so on 2026-08-22 the employee still saw wrong dates
 * and wrong installments. Same contract, same symptoms, different code path.
 *
 * The shop path had one extra bug of its own: its prompt explicitly INSTRUCTED
 * the model to compute payment_value ("إن لم تُذكر صراحةً احسبها: الإيجار السنوي ×
 * عدد سنوات العقد ÷ عدد الدفعات") — guaranteeing a VAT-free installment whenever
 * rent_amount was the pre-VAT annual figure.
 *
 * Figures below come from the real contract RK08 / 20754898102.
 */

function shopPromptText(): string
{
    $m = new ReflectionMethod(ShopAiExtractor::class, 'prompt');
    $m->setAccessible(true);

    return $m->invoke(app(ShopAiExtractor::class));
}

function shopSchemaArray(): array
{
    $m = new ReflectionMethod(ShopAiExtractor::class, 'schema');
    $m->setAccessible(true);

    return $m->invoke(app(ShopAiExtractor::class));
}

// ---------------------------------------------------------------------------
// The shop prompt must carry the same rules as the lease prompt
// ---------------------------------------------------------------------------

it('tells the shop extractor to take the tenancy start, not the sealing date', function () {
    $p = shopPromptText();

    expect($p)->toContain('تاريخ بداية مدة الإيجار');
    expect($p)->toContain('تاريخ إبرام العقد');   // named as the thing to avoid
    expect($p)->toContain('2026-08-12');           // the worked example
});

it('tells the shop extractor that rent_amount includes VAT', function () {
    $p = shopPromptText();

    expect($p)->toContain('شاملاً ضريبة القيمة المضافة');
    expect($p)->toContain('63250');
    expect($p)->toContain('annual_rent');
    expect($p)->toContain('vat_amount');
});

it('no longer instructs the model to COMPUTE the installment value', function () {
    $p = shopPromptText();

    // This instruction was the shop path's own bug: computing from the pre-VAT
    // annual rent guaranteed a VAT-free installment.
    expect($p)->not->toContain('احسبها: الإيجار السنوي');
    expect($p)->toContain('لا تحسبها بنفسك إن كان الجدول مطبوعاً');
});

it('asks the shop extractor for the printed schedule', function () {
    $p = shopPromptText();

    expect($p)->toContain('جدول سداد الدفعات');
    expect($p)->toContain('due_date');
    expect($p)->toContain('تجاهل أعمدة التاريخ الهجري');
});

// ---------------------------------------------------------------------------
// The response schema must actually allow the new fields back
// ---------------------------------------------------------------------------

it('declares the new fields in the shop response schema', function () {
    $props = shopSchemaArray()['properties'];

    expect($props)->toHaveKey('annual_rent');
    expect($props)->toHaveKey('vat_amount');
    expect($props)->toHaveKey('payments');
    expect($props['payments']['type'])->toBe('ARRAY');
    expect($props['payments']['items']['properties'])->toHaveKeys(['payment_no', 'due_date', 'rent_value', 'vat', 'total']);
});

// ---------------------------------------------------------------------------
// The printed schedule must survive the whole chain to the generator
// ---------------------------------------------------------------------------

it('whitelists payments in BOTH the sync and async extract payloads', function () {
    // A whitelist that omits `payments` silently discards it before the form
    // ever sees it — the exact class of bug that hid this for three days.
    $src = file_get_contents(base_path('app/Http/Controllers/Dashboard/ShopController.php'));

    expect(substr_count($src, "'payments' => \$data['payments'] ?? []"))->toBe(1);   // sync
    expect(substr_count($src, "'payments' => \$d['payments'] ?? []"))->toBe(1);      // async
});

it('carries the printed schedule through the shop form as JSON', function () {
    $blade = file_get_contents(base_path('resources/views/dashboard/shop/upd_file.blade.php'));

    expect($blade)->toContain('name="rent_sched_payments"');
    expect($blade)->toContain("setv('rent_sched_payments'");
    expect($blade)->toContain('JSON.stringify(d.payments)');
});

it('decodes the printed schedule from a JSON string or an array', function () {
    $m = new ReflectionMethod(ShopController::class, 'decodePrintedPayments');
    $m->setAccessible(true);
    $c = new ShopController();

    $rows = [
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
        ['payment_no' => 2, 'due_date' => '2027-02-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
    ];

    // From the hidden form field (JSON round-tripped through the browser).
    $fromJson = $m->invoke($c, json_encode($rows));
    expect($fromJson)->toHaveCount(2);
    expect($fromJson[0]['due_date'])->toBe('2026-08-22');
    expect($fromJson[1]['total'])->toBe(31625.00);

    // From a server-side extraction result.
    expect($m->invoke($c, $rows))->toHaveCount(2);

    // Anything unusable degrades to [] so the derived path takes over.
    expect($m->invoke($c, null))->toBe([]);
    expect($m->invoke($c, ''))->toBe([]);
    expect($m->invoke($c, 'not json'))->toBe([]);
    expect($m->invoke($c, '{"broken":'))->toBe([]);
});

it('produces the contract\'s real installments end-to-end, VAT included', function () {
    // Simulates what the shop form now hands the generator.
    $m = new ReflectionMethod(ShopController::class, 'decodePrintedPayments');
    $m->setAccessible(true);

    $payments = $m->invoke(new ShopController(), json_encode([
        ['payment_no' => 1, 'due_date' => '2026-08-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
        ['payment_no' => 2, 'due_date' => '2027-02-22', 'rent_value' => 27500.0, 'vat' => 4125.0, 'total' => 31625.0],
    ]));

    $rows = (new App\Services\LeaseScheduleGenerator())->generate([
        'start_date' => '2026-08-12',
        'end_date' => '2027-08-11',
        'rent_value' => 63250.00,
        'num_payments' => 2,
        'payments' => $payments,
    ]);

    expect($rows)->toHaveCount(2);
    // The bug: 27,500 each (VAT stripped) on 08-12 / 02-12 (derived dates).
    expect($rows[0]['amount'])->toBe(31625.00);
    expect($rows[0]['due_date'])->toBe('2026-08-22');
    expect($rows[1]['amount'])->toBe(31625.00);
    expect($rows[1]['due_date'])->toBe('2027-02-22');
    expect(array_sum(array_column($rows, 'amount')))->toBe(63250.00);
});
