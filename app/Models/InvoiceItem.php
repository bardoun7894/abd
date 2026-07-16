<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line item (بند) of an extracted invoice. Lives in the isolated
 * invoices connection alongside the invoice row (Spec 002 FR-102).
 */
class InvoiceItem extends Model
{
    protected $connection = 'invoices';

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id', 'batch_id', 'line_no', 'name', 'quantity', 'unit',
        'unit_price', 'line_total', 'vat_rate', 'vat_amount', 'confidence',
    ];

    protected $casts = [
        'line_no' => 'integer',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'vat_rate' => 'decimal:3',
        'vat_amount' => 'decimal:2',
        'confidence' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
