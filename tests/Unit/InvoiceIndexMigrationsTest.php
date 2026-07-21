<?php

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 009 bundle C — the two index migrations must be guarded/idempotent (re-run
 * safe) WITHOUT doctrine/dbal (not installed) or Schema::hasIndex (absent in L10),
 * across the isolated 'invoices' connection and the main connection.
 */
beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    // Point the isolated 'invoices' connection at its own in-memory sqlite.
    config()->set('database.connections.invoices', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => false,
    ]);
    // Purge forces fresh (empty) PDOs so each test starts clean.
    DB::purge('sqlite');
    DB::purge('invoices');
    DB::setDefaultConnection('sqlite');

    require_once base_path('database/migrations/invoices/2026_07_21_000015_add_indexes_to_invoices.php');
    require_once base_path('database/migrations/2026_07_21_130000_add_index_to_purchase_no.php');
});

it('indexes invoices.invoice_number + supplier_tax_number idempotently on the invoices connection', function () {
    Schema::connection('invoices')->create('invoices', function ($t) {
        $t->increments('id');
        $t->string('invoice_number')->nullable();
        $t->string('supplier_tax_number', 20)->nullable();
    });

    (new AddIndexesToInvoices())->up();
    (new AddIndexesToInvoices())->up(); // second run must no-op, not throw

    $conn = Schema::connection('invoices')->getConnection();
    expect(AddIndexesToInvoices::indexExists($conn, 'invoices', 'invoices_invoice_number_index'))->toBeTrue();
    expect(AddIndexesToInvoices::indexExists($conn, 'invoices', 'invoices_supplier_tax_number_index'))->toBeTrue();
});

it('adds a non-unique index on purchase.purchase_no idempotently', function () {
    Schema::create('purchase', function ($t) {
        $t->increments('purchase_id');
        $t->string('purchase_no')->nullable();
    });

    (new AddIndexToPurchaseNo())->up();
    (new AddIndexToPurchaseNo())->up(); // second run must no-op, not throw

    $conn = Schema::getConnection();
    expect(AddIndexToPurchaseNo::indexExists($conn, 'purchase', 'purchase_purchase_no_index'))->toBeTrue();

    // Non-unique: two rows sharing purchase_no must be allowed.
    DB::table('purchase')->insert(['purchase_no' => 'DUP-1']);
    DB::table('purchase')->insert(['purchase_no' => 'DUP-1']);
    expect(DB::table('purchase')->where('purchase_no', 'DUP-1')->count())->toBe(2);
});

it('up() is a no-op when the target table/column is absent', function () {
    // No 'purchase' table created — must not throw.
    (new AddIndexToPurchaseNo())->up();
    expect(Schema::hasTable('purchase'))->toBeFalse();
});
