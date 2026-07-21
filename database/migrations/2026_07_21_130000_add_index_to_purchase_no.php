<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 009 bundle C — non-unique index on purchase.purchase_no. Idempotent without
 * doctrine/dbal (NOT installed) or Schema::hasIndex (absent in L10). Index name
 * 'purchase_purchase_no_index' is 26 chars — under Oracle's 30-char identifier limit.
 */
class AddIndexToPurchaseNo extends Migration
{
    public function up()
    {
        $conn = Schema::getConnection();
        if (! Schema::hasTable('purchase') || ! Schema::hasColumn('purchase', 'purchase_no')) {
            return;
        }
        if (self::indexExists($conn, 'purchase', 'purchase_purchase_no_index')) {
            return;
        }
        Schema::table('purchase', function (Blueprint $t) {
            $t->index('purchase_no', 'purchase_purchase_no_index');
        });
    }

    public function down()
    {
        $conn = Schema::getConnection();
        if (self::indexExists($conn, 'purchase', 'purchase_purchase_no_index')) {
            Schema::table('purchase', function (Blueprint $t) {
                $t->dropIndex('purchase_purchase_no_index');
            });
        }
    }

    /** Portable named-index probe (sqlite/mysql/oracle). Fail-closed on driver error. */
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
