<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 001 FR-006 + general governance: append-only audit log of every action on an
 * AI document — read, extract, edit, approve, reject, reprocess, duplicate override.
 * Follows the house `*_history` convention (change_user + change_at, join users at
 * display). Never deleted.
 */
class CreateAiAuditLogTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ai_audit_log')) {
            return;
        }
        Schema::create('ai_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 20)->index();      // invoice | lease
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('action', 30)->index();             // read|extract|edit|approve|reject|reprocess|dup_override
            $table->string('field')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('change_user')->nullable()->index();
            $table->dateTime('change_at')->nullable();
            $table->text('note')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_audit_log');
    }
}
