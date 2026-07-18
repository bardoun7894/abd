<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = config('queue.failed.table', 'failed_jobs');
        $driver = DB::getDriverName();

        if (! Schema::hasTable($table)) {
            return;
        }

        // MySQL/MariaDB: restore AUTO_INCREMENT on the primary key if a legacy
        // install or import left `id` as a plain BIGINT. Without it, inserts that
        // don't specify an id default to 0 and collide ("Duplicate entry '0'").
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        // SQLite already auto-increments INTEGER PRIMARY KEY rows via rowid,
        // and ALTER TABLE MODIFY COLUMN is unsupported, so nothing to do.
        // Oracle uses sequences/triggers for auto-increment; leave it alone.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = config('queue.failed.table', 'failed_jobs');
        $driver = DB::getDriverName();

        if (! Schema::hasTable($table)) {
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // Removing AUTO_INCREMENT is unsafe if rows with id=0 exist, because
            // subsequent inserts with the implicit default 0 would collide on the
            // primary key. If there are multiple id=0 rows we cannot safely
            // renumber them without changing observable data, so skip the rollback.
            $zeroRows = DB::table($table)->where('id', 0)->count();
            if ($zeroRows > 1) {
                return;
            }

            // A single id=0 row can be moved past the current max to keep the PK valid.
            if ($zeroRows === 1) {
                $maxId = (int) DB::table($table)->max('id');
                DB::table($table)->where('id', 0)->update(['id' => $maxId + 1]);
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
