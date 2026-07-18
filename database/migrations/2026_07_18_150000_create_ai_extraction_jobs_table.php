<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 — async interactive extraction. One row per "upload a document, extract it"
 * request: the controller creates it (pending), a queued job fills it (done/failed),
 * and the form polls its status. Lets the web request return instantly instead of
 * blocking on Gemini — provided the queue runs on a worker (QUEUE_CONNECTION != sync).
 */
class CreateAiExtractionJobsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_extraction_jobs')) {
            return;
        }
        Schema::create('ai_extraction_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('module', 40)->index();               // shop | worker | expense | ...
            $table->string('status', 20)->default('pending');    // pending|processing|done|failed
            $table->string('file_path', 1000);                   // stored upload to feed the extractor
            $table->string('file_url', 1000)->nullable();        // served URL for the form
            $table->string('model', 80)->nullable();
            $table->longText('result_json')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_extraction_jobs');
    }
}
