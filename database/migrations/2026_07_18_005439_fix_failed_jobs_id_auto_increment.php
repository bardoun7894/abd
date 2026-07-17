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
        // The default Laravel failed_jobs migration creates `id` as a bigIncrements
        // column, but some legacy installs / imports ended up with `id` as a plain
        // BIGINT with no AUTO_INCREMENT. That causes "Duplicate entry '0'" when
        // multiple failed jobs are inserted.
        DB::statement('ALTER TABLE failed_jobs MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE failed_jobs MODIFY id BIGINT UNSIGNED NOT NULL');
    }
};
