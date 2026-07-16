<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A snapshot of a prior invoice extraction, kept when a reprocess overwrites the
 * live row so no version is ever deleted (governance requirement).
 */
class InvoiceVersion extends Model
{
    public $timestamps = false;

    protected $connection = 'invoices';

    protected $table = 'invoice_versions';

    protected $fillable = [
        'invoice_id', 'batch_id', 'page_number', 'version', 'snapshot', 'created_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];
}
