<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سند قبض/صرف — Spec 008 bundle 1 (cashbox). Append-only: rows are never
 * deleted; a void flips is_void + logs void_reason/void_user/void_date and the
 * matching cashbox_ledger reversal is appended separately by CashboxService.
 */
class CashReceipt extends Model
{
    protected $table = 'cash_receipt';
    protected $primaryKey = 'receipt_id';
    protected $guarded = [];
    public $timestamps = false;

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createUser()
    {
        return $this->belongsTo(User::class, 'create_user');
    }

    public function voidUser()
    {
        return $this->belongsTo(User::class, 'void_user');
    }
}
