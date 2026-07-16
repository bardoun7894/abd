<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 003 (Rentals AI): OCR staging for lease contracts, mirroring the invoice
 * batch/row pattern. Extraction happens here (isolated invoices DB); approved
 * contracts are written to the main-DB `lease_contracts` on user approval.
 */
class CreateLeaseStagingTables extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->create('lease_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('original_filename')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('total_pages')->default(0);
            $table->unsignedInteger('processed_pages')->default(0);
            $table->string('model_used')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->decimal('est_cost_usd', 10, 5)->default(0);
            $table->timestamps();
        });

        Schema::connection('invoices')->create('lease_extractions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->index();
            $table->unsignedInteger('page_number')->default(1);
            $table->string('image_path')->nullable();

            // Extracted lease fields (Spec 003 FR-201) — all nullable
            $table->string('contract_no')->nullable();
            $table->string('tenant_name')->nullable();
            $table->string('tenant_id_no', 40)->nullable();
            $table->string('landlord_name')->nullable();
            $table->string('landlord_id_no', 40)->nullable();
            $table->string('property_no')->nullable();
            $table->string('unit')->nullable();
            $table->string('property_type', 60)->nullable();
            $table->string('address', 1000)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('duration', 60)->nullable();
            $table->decimal('rent_value', 14, 2)->nullable();
            $table->unsignedInteger('num_payments')->nullable();
            $table->decimal('payment_value', 14, 2)->nullable();
            $table->string('payment_frequency', 40)->nullable();
            $table->decimal('deposit', 14, 2)->nullable();
            $table->string('payment_method', 60)->nullable();
            $table->text('renewal_terms')->nullable();
            $table->text('cancellation_terms')->nullable();
            $table->text('increase_terms')->nullable();
            $table->text('extra_terms')->nullable();

            $table->json('raw_json')->nullable();
            $table->json('field_confidence')->nullable();
            $table->text('extracted_text')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('status')->default('pending')->index();  // pending|done|failed
            $table->boolean('needs_review')->default(false)->index();
            $table->text('validation_notes')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('superseded_by')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();  // set on approval -> main-DB lease_contracts
            $table->timestamp('mapped_at')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('lease_batches')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::connection('invoices')->dropIfExists('lease_extractions');
        Schema::connection('invoices')->dropIfExists('lease_batches');
    }
}
