<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Spec 003 (Rentals AI) staging row — one OCR-extracted lease contract page/document,
 * mirroring the Invoice model. Lives in the isolated `invoices` connection until a
 * user approves it, at which point a LeaseContract is created in the main DB and
 * `contract_id`/`mapped_at` are set here.
 */
class LeaseExtraction extends Model
{
    /** Isolated connection — never touches the main app schema. */
    protected $connection = 'invoices';

    protected $table = 'lease_extractions';

    protected $fillable = [
        'batch_id', 'page_number', 'image_path',
        'contract_no', 'tenant_name', 'tenant_id_no',
        'landlord_name', 'landlord_id_no',
        'property_no', 'unit', 'property_type', 'address',
        'start_date', 'end_date', 'duration',
        'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
        'deposit', 'payment_method',
        'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
        'raw_json', 'field_confidence', 'extracted_text', 'confidence',
        'status', 'needs_review', 'validation_notes', 'error_message',
        'version', 'superseded_by', 'contract_id', 'mapped_at',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_value' => 'decimal:2',
        'num_payments' => 'integer',
        'payment_value' => 'decimal:2',
        'deposit' => 'decimal:2',
        'raw_json' => 'array',
        'field_confidence' => 'array',
        'confidence' => 'decimal:2',
        'needs_review' => 'boolean',
        'version' => 'integer',
        'superseded_by' => 'integer',
        'contract_id' => 'integer',
        'mapped_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LeaseBatch::class, 'batch_id');
    }
}
