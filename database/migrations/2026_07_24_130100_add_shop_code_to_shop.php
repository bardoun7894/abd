<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F3 — manual, unique shop code (e.g. A1, A2, B1) alongside
 * shop_name. Never auto-generated. ADDITIVE + nullable only; hasColumn/
 * index-existence guarded for the legacy `shop` table (predates the
 * migration set, no create-migration) so this is safe to re-run on both
 * Oracle and MySQL. Follows the portable named-index probe already used by
 * 2026_07_21_130000_add_index_to_purchase_no.php (no doctrine/dbal,
 * no Schema::hasIndex — absent in Laravel 10).
 */
class AddShopCodeToShop extends Migration
{
    private const UNIQUE_INDEX = 'shop_shop_code_unique';

    public function up()
    {
        if (! Schema::hasTable('shop')) {
            return;
        }

        if (! Schema::hasColumn('shop', 'shop_code')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->string('shop_code', 50)->nullable();
            });
        }

        if (! self::indexExists(Schema::getConnection(), 'shop', self::UNIQUE_INDEX)) {
            Schema::table('shop', function (Blueprint $table) {
                $table->unique('shop_code', self::UNIQUE_INDEX);
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('shop')) {
            return;
        }

        if (self::indexExists(Schema::getConnection(), 'shop', self::UNIQUE_INDEX)) {
            Schema::table('shop', function (Blueprint $table) {
                $table->dropUnique(self::UNIQUE_INDEX);
            });
        }

        if (Schema::hasColumn('shop', 'shop_code')) {
            Schema::table('shop', function (Blueprint $table) {
                $table->dropColumn('shop_code');
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
