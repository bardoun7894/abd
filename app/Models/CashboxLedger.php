<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Running cash ledger row — Spec 008 bundle 1 (cashbox). Append-only source for
 * the /cashbox DataTable and balance queries. Never updated/deleted.
 */
class CashboxLedger extends Model
{
    protected $table = 'cashbox_ledger';
    protected $primaryKey = 'entry_id';
    protected $guarded = [];
    public $timestamps = false;

    public function receipt()
    {
        return $this->belongsTo(CashReceipt::class, 'receipt_id', 'receipt_id');
    }

    public function changeUser()
    {
        return $this->belongsTo(User::class, 'change_user');
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('change_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('change_at', '<=', $to);
        }

        return $query;
    }

    public function scopeSourceType(Builder $query, ?string $sourceType): Builder
    {
        return $sourceType ? $query->where('source_type', $sourceType) : $query;
    }

    public function scopeChangedBy(Builder $query, $userId): Builder
    {
        return $userId ? $query->where('change_user', $userId) : $query;
    }
}
