<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 009 bundle C — index the two columns the invoices subsystem filters by at
 * scale. Guarded/idempotent WITHOUT doctrine/dbal (NOT installed in this project —
 * only a Laravel suggestion) and WITHOUT Schema::hasIndex (absent in L10), via a
 * driver-portable named-index probe (sqlite/mysql/oracle).
 */
class AddIndexesToInvoices extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        $conn = Schema::connection('invoices')->getConnection();
        if (! Schema::connection('invoices')->hasTable('invoices')) {
            return;
        }
        if (Schema::connection('invoices')->hasColumn('invoices', 'invoice_number')
            && ! self::indexExists($conn, 'invoices', 'invoices_invoice_number_index')) {
            Schema::connection('invoices')->table('invoices', function (Blueprint $t) {
                $t->index('invoice_number', 'invoices_invoice_number_index');
            });
        }
        if (Schema::connection('invoices')->hasColumn('invoices', 'supplier_tax_number')
            && ! self::indexExists($conn, 'invoices', 'invoices_supplier_tax_number_index')) {
            Schema::connection('invoices')->table('invoices', function (Blueprint $t) {
                $t->index('supplier_tax_number', 'invoices_supplier_tax_number_index');
            });
        }
    }

    public function down()
    {
        $conn = Schema::connection('invoices')->getConnection();
        foreach (['invoices_invoice_number_index', 'invoices_supplier_tax_number_index'] as $idx) {
            if (self::indexExists($conn, 'invoices', $idx)) {
                Schema::connection('invoices')->table('invoices', function (Blueprint $t) use ($idx) {
                    $t->dropIndex($idx);
                });
            }
        }
    }

    /** Portable 'does this named index exist' probe. Fail-closed on any driver error. */
    public static function indexExists($conn, string $table, string $index): bool
    {
        $driver = $conn->getDriverName();
        try {
            if ($driver === 'sqlite') {
                return (bool) $conn->selectOne(
                    "select name from sqlite_master where type = 'index' and tbl_name = ? and name = ?",
                    [$table, $index]
                );
            }
            if ($driver === 'mysql') {
                return (bool) $conn->selectOne(
                    'select 1 as x from information_schema.statistics where table_schema = database() and table_name = ? and index_name = ? limit 1',
                    [$table, $index]
                );
            }
            if (in_array($driver, ['oracle', 'oci8'], true)) {
                return (bool) $conn->selectOne(
                    'select index_name from user_indexes where index_name = ? and table_name = ?',
                    [strtoupper($index), strtoupper($table)]
                );
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
