<?php

// نهلة الوادي prints a bilingual header, so Gemini returns a different slice of it
// on every invoice — the same company ended up stored under 34 spellings on نور
// الصباح and 16 on صباح النور, all with tax 300975259400003. The supplier LINK was
// always right (all 1,012 → supplier #1), but purchase.purchase_respon kept the raw
// text, so searching «اسم المورد» found only a slice: «نهلة الوادي للتجارة» → 662
// of 1,012, «نهلة الوادي التجارية» → 175. One company, one name.
uses(Tests\TestCase::class);

use App\Models\Supplier;
use App\Services\InvoicePurchaseMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('suppliers');
    Schema::create('suppliers', function ($t) {
        $t->id();
        $t->string('name')->nullable();
        $t->string('tax_number')->nullable();
        $t->string('cr_number')->nullable();
        $t->unsignedBigInteger('create_user')->nullable();
        $t->timestamps();
        $t->softDeletes();
    });
    Supplier::create(['name' => 'شركة نهلة الوادي للتجارة', 'tax_number' => '300975259400003']);
});

it('uses the supplier master name when the tax number identifies a known company', function () {
    // Every one of these came off a real نهلة invoice.
    $variants = [
        'Nahla Al Wadi Trading Co. LLC',
        'Nahla Al Wadi Trading Co, LLC',
        'شركة نهلة الوادي التجارية',
        "شركة نهلة الوادي للتجارة\nNahla Al Wadi Trading Co. LLC",
        'Nahla Al Wadi Trading Co. LLC شركة نهلة الوادي للتجارة',
        'Nahla',
    ];
    foreach ($variants as $raw) {
        expect(InvoicePurchaseMapper::canonicalSupplierName('300975259400003', $raw))
            ->toBe('شركة نهلة الوادي للتجارة');
    }
});

it('keeps what was printed when the tax number is unknown — never invents a supplier', function () {
    expect(InvoicePurchaseMapper::canonicalSupplierName('311102341900003', 'CAESAR MANUFACTURING CO.'))
        ->toBe('CAESAR MANUFACTURING CO.');
    expect(InvoicePurchaseMapper::canonicalSupplierName(null, 'مؤسسة عبد العزيز يوسف البوروزة'))
        ->toBe('مؤسسة عبد العزيز يوسف البوروزة');
    expect(InvoicePurchaseMapper::canonicalSupplierName('', ''))->toBeNull();
});

it('tolerates the tax number arriving with spaces or Arabic-Indic digits', function () {
    expect(InvoicePurchaseMapper::canonicalSupplierName('300 975 259 400 003', 'Nahla'))
        ->toBe('شركة نهلة الوادي للتجارة');
});

it('does not overwrite with a blank master name', function () {
    Supplier::create(['name' => null, 'tax_number' => '399999999999993']);
    expect(InvoicePurchaseMapper::canonicalSupplierName('399999999999993', 'مورد من الفاتورة'))
        ->toBe('مورد من الفاتورة');
});

it('a purchase row built for a known supplier carries the canonical name', function () {
    $row = InvoicePurchaseMapper::buildPurchaseRow([
        'invoice_number' => 'NHD252471254',
        'invoice_date' => '2026-08-29',
        'total_incl_vat' => 580.42,
        'supplier_tax_number' => '300975259400003',
        'supplier_name' => 'Nahla Al Wadi Trading Co, LLC',
    ], 133, null, 11);

    expect($row['purchase_respon'])->toBe('شركة نهلة الوادي للتجارة');
    expect($row['tax_number'])->toBe('300975259400003');
    expect($row['purchase_no'])->toBe('NHD252471254');
});

// The first dry run over every supplier would have renamed unrelated companies:
// tax "300" (three digits) merged «محطة سهل» with «WALEED TURNERY», and one
// misread 15-digit number pulled 22 different vendors under one name. Tax numbers
// are OCR'd like everything else, so a sweep is opt-in and format-checked.
it('refuses to sweep every supplier unless explicitly asked', function () {
    \Illuminate\Support\Facades\Artisan::call('purchases:unify-supplier-names', ['--dry-run' => true]);
    expect(\Illuminate\Support\Facades\Artisan::output())
        ->toContain('--tax')
        ->toContain('--all');
});

it('skips tax numbers that are not a real 15-digit Saudi VAT number', function () {
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax('300975259400003'))->toBeTrue();
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax('300'))->toBeFalse();          // the 3-digit one
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax('30095581700003'))->toBeFalse(); // 14 digits
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax('100975259400003'))->toBeFalse(); // must start with 3
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax('300975259400001'))->toBeFalse(); // must end with 3
    expect(App\Console\Commands\UnifySupplierNames::isPlausibleTax(null))->toBeFalse();
});

it('purchases:unify-supplier-names rewrites existing rows and dry-run writes nothing', function () {
    Schema::dropIfExists('purchase');
    Schema::create('purchase', function ($t) {
        $t->increments('purchase_id');
        $t->string('purchase_no')->nullable();
        $t->string('purchase_respon')->nullable();
        $t->string('tax_number')->nullable();
        $t->unsignedBigInteger('supplier_id')->nullable();
    });
    $sid = Supplier::where('tax_number', '300975259400003')->value('id');
    DB::table('purchase')->insert([
        ['purchase_no' => 'A', 'purchase_respon' => 'Nahla Al Wadi Trading Co. LLC', 'tax_number' => '300975259400003', 'supplier_id' => $sid],
        ['purchase_no' => 'B', 'purchase_respon' => 'شركة نهلة الوادي التجارية', 'tax_number' => '300975259400003', 'supplier_id' => $sid],
        ['purchase_no' => 'C', 'purchase_respon' => 'شركة نهلة الوادي للتجارة', 'tax_number' => '300975259400003', 'supplier_id' => $sid],
        // a different supplier must not be touched
        ['purchase_no' => 'D', 'purchase_respon' => 'CAESAR MANUFACTURING CO.', 'tax_number' => '311102341900003', 'supplier_id' => null],
    ]);

    $opts = ['--tax' => '300975259400003'];
    \Illuminate\Support\Facades\Artisan::call('purchases:unify-supplier-names', $opts + ['--dry-run' => true]);
    expect(\Illuminate\Support\Facades\Artisan::output())->toContain('[dry-run]')->toContain('2');
    expect(DB::table('purchase')->where('purchase_no', 'A')->value('purchase_respon'))->toBe('Nahla Al Wadi Trading Co. LLC');

    \Illuminate\Support\Facades\Artisan::call('purchases:unify-supplier-names', $opts);
    expect(DB::table('purchase')->where('purchase_no', 'A')->value('purchase_respon'))->toBe('شركة نهلة الوادي للتجارة');
    expect(DB::table('purchase')->where('purchase_no', 'B')->value('purchase_respon'))->toBe('شركة نهلة الوادي للتجارة');
    expect(DB::table('purchase')->where('purchase_no', 'C')->value('purchase_respon'))->toBe('شركة نهلة الوادي للتجارة');
    expect(DB::table('purchase')->where('purchase_no', 'D')->value('purchase_respon'))->toBe('CAESAR MANUFACTURING CO.');
});
