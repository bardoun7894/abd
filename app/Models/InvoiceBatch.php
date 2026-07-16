<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceBatch extends Model
{
    /** Isolated connection — never touches the main app schema. */
    protected $connection = 'invoices';

    protected $table = 'invoice_batches';

    protected $fillable = [
        'user_id', 'original_filename', 'pdf_path', 'bus_batch_id',
        'status', 'total_pages', 'processed_pages', 'grand_total',
        'model_used', 'error_message',
        'input_tokens', 'output_tokens', 'est_cost_usd',
    ];

    protected $casts = [
        'total_pages' => 'integer',
        'processed_pages' => 'integer',
        'grand_total' => 'decimal:2',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'est_cost_usd' => 'decimal:5',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'batch_id');
    }

    /** Sum total_incl_vat across this batch's invoices and persist it. */
    public function recomputeGrandTotal(): float
    {
        $total = (float) $this->invoices()->sum('total_incl_vat');
        $this->forceFill(['grand_total' => $total])->save();

        return $total;
    }
}
