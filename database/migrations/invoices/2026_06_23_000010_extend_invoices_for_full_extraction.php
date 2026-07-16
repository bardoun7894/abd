<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 002 (Purchases AI): extend the extraction row with the full field set the
 * client requires — invoice type, currency, discount, commercial registration,
 * payment method, due date — plus per-field confidence, a file fingerprint for
 * duplicate detection, and version columns for reprocess-without-delete (Spec 001).
 * All additive + nullable so existing rows and the current pipeline keep working.
 */
class ExtendInvoicesForFullExtraction extends Migration
{
    protected $connection = 'invoices';

    public function up()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            if (! Schema::connection('invoices')->hasColumn('invoices', 'invoice_type')) {
                $table->string('invoice_type', 20)->nullable();            // tax | simplified (ضريبية | مبسطة)
                $table->string('currency', 10)->nullable();
                $table->decimal('discount_total', 14, 2)->nullable();
                $table->decimal('vat_rate', 6, 3)->nullable();
                $table->string('commercial_registration', 30)->nullable();
                $table->string('payment_method', 60)->nullable();
                $table->date('due_date')->nullable();
                $table->json('field_confidence')->nullable();              // { field: 0..1 } per Spec 001 FR-002
                $table->string('file_hash', 64)->nullable()->index();      // sha256 for dedup (Spec 002 FR-106)
                $table->unsignedInteger('version')->default(1);            // reprocess versioning (Spec 001 FR-004)
                $table->unsignedBigInteger('superseded_by')->nullable();
                $table->unsignedInteger('processing_ms')->nullable();      // avg-time metric (Spec 001 FR-009)
            }
        });
    }

    public function down()
    {
        Schema::connection('invoices')->table('invoices', function (Blueprint $table) {
            foreach ([
                'invoice_type', 'currency', 'discount_total', 'vat_rate', 'commercial_registration',
                'payment_method', 'due_date', 'field_confidence', 'file_hash', 'version',
                'superseded_by', 'processing_ms',
            ] as $col) {
                if (Schema::connection('invoices')->hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
