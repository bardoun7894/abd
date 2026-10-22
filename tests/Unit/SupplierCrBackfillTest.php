<?php

// suppliers:backfill-cr and learnCrNumber() write a supplier's identity, so both must
// refuse anything short of clean evidence.
uses(Tests\TestCase::class);

use App\Models\Supplier;
use App\Services\InvoicePurchaseMapper;
use Illuminate\Support\Facades\Artisan;
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
    Schema::dropIfExists('purchase');
    Schema::create('purchase', function ($t) {
        $t->increments('purchase_id');
        $t->string('tax_number')->nullable();
        $t->string('commercial_registration')->nullable();
    });
});

function invoicesWithCr(string $tax, array $crs): void
{
    foreach ($crs as $cr) {
        DB::table('purchase')->insert(['tax_number' => $tax, 'commercial_registration' => $cr]);
    }
}

it('fills the CR when every invoice agrees', function () {
    $s = Supplier::create(['name' => 'Nahla', 'tax_number' => '300975259400003']);
    invoicesWithCr('300975259400003', ['2050092979', 'C.R. 2050092979']);

    Artisan::call('suppliers:backfill-cr');

    expect(Supplier::find($s->id)->cr_number)->toBe('2050092979');
});

it('treats ANY disagreement as contested, even a 3-to-1 majority', function () {
    $s = Supplier::create(['name' => 'Nahla', 'tax_number' => '300975259400003']);
    invoicesWithCr('300975259400003', ['2050092979', '2050092979', '2050092979', '2050092970']);

    Artisan::call('suppliers:backfill-cr');

    expect(Artisan::output())->toContain('contested=1')->toContain('filled=0');
    expect(Supplier::find($s->id)->cr_number)->toBeEmpty();
});

it('ignores malformed CR readings when deciding agreement', function () {
    $s = Supplier::create(['name' => 'Nahla', 'tax_number' => '300975259400003']);
    invoicesWithCr('300975259400003', ['2050092979', '2050092979', '205']);

    Artisan::call('suppliers:backfill-cr');

    expect(Supplier::find($s->id)->cr_number)->toBe('2050092979');
});

it('learnCrNumber does not teach a CR to a supplier matched on an implausible tax number', function () {
    $learn = new ReflectionMethod(InvoicePurchaseMapper::class, 'learnCrNumber');
    $learn->setAccessible(true);

    $misread = Supplier::create(['name' => 'محطة سهل', 'tax_number' => '300']);
    $learn->invoke(null, $misread, '2050092979');
    expect(Supplier::find($misread->id)->cr_number)->toBeEmpty();

    $good = Supplier::create(['name' => 'Nahla', 'tax_number' => '300975259400003']);
    $learn->invoke(null, $good, '2050092979');
    expect(Supplier::find($good->id)->cr_number)->toBe('2050092979');
});