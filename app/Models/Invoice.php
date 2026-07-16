<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    /** Isolated connection — never touches the main app schema. */
    protected $connection = 'invoices';

    protected $table = 'invoices';

    protected $fillable = [
        'batch_id', 'page_number', 'image_path',
        'supplier_name', 'supplier_tax_number', 'invoice_number',
        'invoice_date', 'invoice_date_raw',
        'amount_before_vat', 'vat_amount', 'total_incl_vat',
        'raw_json', 'confidence', 'image_quality', 'status', 'needs_review',
        'validation_notes', 'error_message',
        'purchase_id', 'mapped_at',
        // Spec 002/001 — extended fields.
        'invoice_type', 'currency', 'discount_total', 'vat_rate',
        'commercial_registration', 'payment_method', 'due_date',
        'field_confidence', 'file_hash', 'version', 'superseded_by', 'processing_ms',
        'issuer_establishment_name', 'notes',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'invoice_date' => 'date',
        'amount_before_vat' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_incl_vat' => 'decimal:2',
        'confidence' => 'decimal:2',
        'needs_review' => 'boolean',
        'raw_json' => 'array',
        'purchase_id' => 'integer',
        'mapped_at' => 'datetime',
        // Spec 002/001 — extended casts.
        'discount_total' => 'decimal:2',
        'vat_rate' => 'decimal:3',
        'due_date' => 'date',
        'field_confidence' => 'array',
        'version' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InvoiceBatch::class, 'batch_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
}
