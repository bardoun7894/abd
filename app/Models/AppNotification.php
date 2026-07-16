<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Spec 001 FR-007: multi-channel alert layer (Rentals module).
 *
 * Backs the `app_notifications` table. Written by App\Services\AlertDispatcher
 * and readable by the existing bell (see HomeController::notify_num/load_alerts)
 * once that controller is wired to also count these rows.
 */
class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'ref_type',
        'ref_id',
        'is_read',
        'sent_email',
        'sent_sms',
        'dedup_key',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'ref_id' => 'integer',
        'is_read' => 'boolean',
        'sent_email' => 'boolean',
        'sent_sms' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
