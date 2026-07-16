<?php

namespace App\Services;

use App\Mail\AlertMail;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Spec 001 FR-007: single entry point for the multi-channel alert layer
 * (Rentals module). Writes an in-app row to app_notifications and,
 * optionally, sends the same alert over email and/or SMS.
 *
 * Never throws: every channel is wrapped in its own try/catch so a failure
 * in one channel (e.g. SMTP down) never blocks the others.
 */
class AlertDispatcher
{
    /**
     * @param  array{
     *     ref_type?: ?string,
     *     ref_id?: ?int,
     *     email?: bool,
     *     sms?: bool,
     *     phone?: ?string,
     *     dedup_key?: ?string,
     * }  $opts
     */
    public static function send(int $userId, string $type, string $title, string $body, array $opts = []): void
    {
        $dedupKey = $opts['dedup_key'] ?? null;

        if ($dedupKey !== null && $dedupKey !== '' && self::isDuplicate($dedupKey)) {
            return;
        }

        $notification = self::createInAppNotification($userId, $type, $title, $body, $opts, $dedupKey);

        if (! empty($opts['email'])) {
            self::sendEmailChannel($userId, $title, $body, $notification);
        }

        if (! empty($opts['sms']) && ! empty($opts['phone'])) {
            self::sendSmsChannel($opts['phone'], $body, $notification);
        }
    }

    protected static function isDuplicate(string $dedupKey): bool
    {
        try {
            return AppNotification::where('dedup_key', $dedupKey)->exists();
        } catch (Throwable $e) {
            Log::error('AlertDispatcher: dedup lookup failed - '.$e->getMessage());

            return false;
        }
    }

    protected static function createInAppNotification(
        int $userId,
        string $type,
        string $title,
        string $body,
        array $opts,
        ?string $dedupKey
    ): ?AppNotification {
        try {
            return AppNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'ref_type' => $opts['ref_type'] ?? null,
                'ref_id' => $opts['ref_id'] ?? null,
                'dedup_key' => $dedupKey,
            ]);
        } catch (Throwable $e) {
            Log::error('AlertDispatcher: failed to persist in-app notification - '.$e->getMessage());

            return null;
        }
    }

    protected static function sendEmailChannel(int $userId, string $title, string $body, ?AppNotification $notification): void
    {
        try {
            $user = User::find($userId);

            if (! $user || empty($user->email)) {
                return;
            }

            Mail::to($user->email)->send(new AlertMail($title, $body));

            if ($notification) {
                $notification->sent_email = true;
                $notification->save();
            }
        } catch (Throwable $e) {
            Log::error('AlertDispatcher: failed to send email channel - '.$e->getMessage());
        }
    }

    protected static function sendSmsChannel(string $phone, string $body, ?AppNotification $notification): void
    {
        try {
            $sent = (new SmsClient())->send($phone, $body);

            if ($sent && $notification) {
                $notification->sent_sms = true;
                $notification->save();
            }
        } catch (Throwable $e) {
            Log::error('AlertDispatcher: failed to send SMS channel - '.$e->getMessage());
        }
    }
}
