<?php

use App\Services\InvoiceAnomalyDetector;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->det = new InvoiceAnomalyDetector();
});

/** A clean invoice that should trip no rules (uses a fictitious supplier so the
 *  guarded supplier-average DB lookup never finds >=3 priors to compare against). */
function cleanInvoice(array $overrides = []): array
{
    return array_merge([
        'supplier_name' => 'مورد اختبار فريد لا يتكرر '.uniqid(),
        'supplier_tax_number' => '300097525940003',
        'invoice_number' => 'ANOM-1',
        'invoice_date' => Carbon::now()->subDays(10)->toDateString(),
        'amount_before_vat' => 100,
        'vat_amount' => 15,
        'total_incl_vat' => 115,
    ], $overrides);
}

it('returns no anomalies for a clean invoice', function () {
    $notes = $this->det->detect(cleanInvoice());
    expect($notes)->toBe([]);
});

// ---- Rule 1: VAT rate --------------------------------------------------------------

it('flags a VAT rate that deviates more than 2 points from 15%', function () {
    $notes = $this->det->detect(cleanInvoice(['amount_before_vat' => 100, 'vat_amount' => 25])); // 25%
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'نسبة الضريبة')))->toBeTrue();
});

it('does not flag a VAT rate within 2 points of 15%', function () {
    $notes = $this->det->detect(cleanInvoice(['amount_before_vat' => 100, 'vat_amount' => 16.5])); // 16.5%
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'نسبة الضريبة')))->toBeFalse();
});

it('does not flag a zero-rated invoice (near-zero VAT)', function () {
    $notes = $this->det->detect(cleanInvoice(['amount_before_vat' => 100, 'vat_amount' => 0.001, 'total_incl_vat' => 100.001]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'نسبة الضريبة')))->toBeFalse();
});

// ---- Rule 2: total mismatch ---------------------------------------------------------

it('flags a total that does not reconcile with base + vat beyond tolerance', function () {
    $notes = $this->det->detect(cleanInvoice(['amount_before_vat' => 1000, 'vat_amount' => 150, 'total_incl_vat' => 1300]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'فرق بين الإجمالي')))->toBeTrue();
});

it('does not flag a total within tolerance of base + vat', function () {
    $notes = $this->det->detect(cleanInvoice(['amount_before_vat' => 1000, 'vat_amount' => 150, 'total_incl_vat' => 1150.05]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'فرق بين الإجمالي')))->toBeFalse();
});

// ---- Rule 3: future date ------------------------------------------------------------

it('flags an invoice_date in the future', function () {
    $notes = $this->det->detect(cleanInvoice(['invoice_date' => Carbon::now()->addDays(5)->toDateString()]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'المستقبل')))->toBeTrue();
});

it('does not flag today\'s date as future', function () {
    $notes = $this->det->detect(cleanInvoice(['invoice_date' => Carbon::now()->toDateString()]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'المستقبل')))->toBeFalse();
});

// ---- Rule 4: stale date ---------------------------------------------------------------

it('flags an invoice_date older than 3 years', function () {
    $notes = $this->det->detect(cleanInvoice(['invoice_date' => Carbon::now()->subYears(4)->toDateString()]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'قديم جدًا')))->toBeTrue();
});

it('does not flag a recent date as stale', function () {
    $notes = $this->det->detect(cleanInvoice(['invoice_date' => Carbon::now()->subMonths(6)->toDateString()]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'قديم جدًا')))->toBeFalse();
});

// ---- Rule 5: zero/negative total -------------------------------------------------------

it('flags a zero total_incl_vat', function () {
    $notes = $this->det->detect(cleanInvoice(['total_incl_vat' => 0]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'صفر أو سالب')))->toBeTrue();
});

it('flags a negative total_incl_vat', function () {
    $notes = $this->det->detect(cleanInvoice(['total_incl_vat' => -50]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'صفر أو سالب')))->toBeTrue();
});

it('does not flag a positive total_incl_vat', function () {
    $notes = $this->det->detect(cleanInvoice(['total_incl_vat' => 115]));
    expect(collect($notes)->contains(fn ($n) => str_contains($n, 'صفر أو سالب')))->toBeFalse();
});

// ---- Rule 6: supplier-average deviation (guarded DB lookup) -----------------------------

it('never throws when the DB/connection is unavailable (guarded lookup)', function () {
    // No Laravel app booted in this pure unit test — the DB facade has no root.
    // detect() must swallow that and simply skip the supplier-average rule.
    $notes = $this->det->detect(cleanInvoice(), 'invoices');
    expect($notes)->toBe([]);
});
