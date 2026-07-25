<?php

/**
 * Spec 024 F1 — the two F1 migrations must actually execute (not just a
 * hand-rolled schema): the transfer-fields migration on the isolated `invoices`
 * connection, and the reroute-permission seed on the default connection. Both
 * are additive, hasColumn/exists-guarded, and idempotent (safe to re-run).
 */

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // require inside beforeEach — base_path() needs the booted app (top-level
    // require runs during collection, before Laravel bootstraps).
    require_once base_path('database/migrations/invoices/2026_07_24_000016_add_transfer_fields_to_invoices.php');
    require_once base_path('database/migrations/2026_07_24_000017_seed_invoice_reroute_permission.php');

    // Point BOTH the default and the 'invoices' connection at isolated memory DBs.
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    config()->set('database.connections.invoices', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    DB::purge('sqlite');
    DB::purge('invoices');
    DB::setDefaultConnection('sqlite');
});

it('runs the transfer-fields migration on the invoices connection and is idempotent', function () {
    Schema::connection('invoices')->create('invoices', function ($t) {
        $t->increments('id');
        $t->dateTime('mapped_at')->nullable();
    });

    (new AddTransferFieldsToInvoices())->up();
    (new AddTransferFieldsToInvoices())->up(); // re-run must no-op via hasColumn guard, not throw

    expect(Schema::connection('invoices')->hasColumn('invoices', 'transferred_branch_label'))->toBeTrue();
    expect(Schema::connection('invoices')->hasColumn('invoices', 'transferred_at'))->toBeTrue();
    expect(Schema::connection('invoices')->hasColumn('invoices', 'transferred_by'))->toBeTrue();

    (new AddTransferFieldsToInvoices())->down();
    expect(Schema::connection('invoices')->hasColumn('invoices', 'transferred_branch_label'))->toBeFalse();
});

it('no-ops the transfer migration when the invoices table is absent (guard)', function () {
    // No `invoices` table created — up() must return early, not fatal.
    (new AddTransferFieldsToInvoices())->up();
    expect(Schema::connection('invoices')->hasTable('invoices'))->toBeFalse();
});

it('seeds the reroute per_function id 222 and is idempotent', function () {
    Schema::create('per_controller', function ($t) {
        $t->integer('id')->primary();
        $t->string('name')->nullable();
        $t->string('controller_name')->nullable();
        $t->integer('is_delete')->nullable();
        $t->integer('order_c')->nullable();
        $t->integer('is_active')->nullable();
    });
    Schema::create('per_function', function ($t) {
        $t->integer('id')->primary();
        $t->integer('parent_id')->nullable();
        $t->string('name')->nullable();
        $t->integer('is_delete')->nullable();
        $t->integer('order_p')->nullable();
        $t->integer('is_branch')->nullable();
    });

    (new SeedInvoiceReroutePermission())->up();
    (new SeedInvoiceReroutePermission())->up(); // re-run must not duplicate id 222

    $rows = DB::table('per_function')->where('id', 222)->get();
    expect($rows)->toHaveCount(1);
    expect($rows->first()->parent_id)->toBe(100);
});

it('no-ops the reroute seed when the permission tables are absent (guard)', function () {
    // per_controller / per_function not created — up() must return early, not fatal.
    (new SeedInvoiceReroutePermission())->up();
    expect(true)->toBeTrue();
});
