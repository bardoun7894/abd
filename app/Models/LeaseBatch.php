<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Spec 003 (Rentals AI) upload batch — mirrors InvoiceBatch. One PDF upload =
 * one batch, containing one or more page extractions.
 */
class LeaseBatch extends Model
{
    /** Isolated connection — never touches the main app schema. */
    protected $connection = 'invoices';

    protected $table = 'lease_batches';

    protected $fillable = [
        'user_id', 'original_filename', 'pdf_path',
        'status', 'total_pages', 'processed_pages',
        'model_used', 'error_message',
        'input_tokens', 'output_tokens', 'est_cost_usd',
    ];

    protected $casts = [
        'total_pages' => 'integer',
        'processed_pages' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'est_cost_usd' => 'decimal:5',
    ];

    public function extractions(): HasMany
    {
        return $this->hasMany(LeaseExtraction::class, 'batch_id');
    }
}
