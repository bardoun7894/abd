<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Spec 003 FR-202 — the approved lease/contract entity (main DB). Created from a
 * LeaseExtraction on user approval; owns the generated LeasePayment schedule.
 */
class LeaseContract extends Model
{
    use SoftDeletes;

    protected $table = 'lease_contracts';

    protected $fillable = [
        'contract_no', 'shop_id',
        'tenant_name', 'tenant_id_no',
        'landlord_name', 'landlord_id_no',
        'property_no', 'unit', 'property_type', 'address',
        'start_date', 'end_date', 'duration',
        'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
        'deposit', 'payment_method',
        'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
        'attach_url', 'extracted_text', 'status',
        'extraction_id', 'create_user',
    ];

    protected $casts = [
        'shop_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_value' => 'decimal:2',
        'num_payments' => 'integer',
        'payment_value' => 'decimal:2',
        'deposit' => 'decimal:2',
        'extraction_id' => 'integer',
        'create_user' => 'integer',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(LeasePayment::class, 'contract_id');
    }
}
