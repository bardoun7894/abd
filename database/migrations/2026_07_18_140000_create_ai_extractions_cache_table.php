<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI result cache: dedup identical extraction calls so the same document is never
 * paid for twice. Keyed by a hash of (model | prompt | schema | file hash) so any
 * caller (interactive extractors + background pipelines) that re-sends the same
 * file with the same intent gets an instant, zero-cost cached result.
 */
class CreateAiExtractionsCacheTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_extractions')) {
            return;
        }
        Schema::create('ai_extractions', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 64)->unique();
            $table->string('module', 60)->nullable()->index();
            $table->string('model', 80)->nullable();
            $table->string('file_hash', 64)->nullable()->index();
            $table->longText('result_json');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('est_cost_usd', 12, 6)->default(0);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_extractions');
    }
}
