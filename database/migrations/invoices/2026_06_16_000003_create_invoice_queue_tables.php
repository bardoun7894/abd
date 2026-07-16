<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Queue + batch tables on the isolated invoices connection, so the async
 * production path (Bus::batch of ProcessInvoicePage jobs) stays out of the
 * main app schema. Only needed when QUEUE_CONNECTION=database for this feature.
 */
class CreateInvoiceQueueTables extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        $schema = Schema::connection('invoices');

        if (! $schema->hasTable('jobs')) {
            $schema->create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! $schema->hasTable('job_batches')) {
            $schema->create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! $schema->hasTable('failed_jobs')) {
            $schema->create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        $schema = Schema::connection('invoices');
        $schema->dropIfExists('jobs');
        $schema->dropIfExists('job_batches');
        $schema->dropIfExists('failed_jobs');
    }
}
