<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Spec 007 — single-row AI subscription config gating all Gemini extraction
 * (invoices, leases, shop, ...). current() is fail-safe: a missing table or
 * a DB outage never blocks/500s the app — it returns an unsaved, unblocked
 * in-memory default row (mirrors App\Services\Settings's cache/DB fallback
 * philosophy so extraction degrades to "allowed" rather than crashing).
 */
class AiSubscription extends Model
{
    protected $table = 'ai_subscriptions';

    protected $fillable = [
        'active', 'starts_at', 'expires_at', 'quota_pages', 'used_pages', 'renewed_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'quota_pages' => 'integer',
        'used_pages' => 'integer',
        'renewed_at' => 'datetime',
    ];

    /**
     * The single active-row config. Creates a sensible (unblocked) default
     * row if none exists yet (fresh install). Never throws — a missing
     * table / unreachable DB falls back to an unsaved, unblocked instance.
     */
    public static function current(): self
    {
        try {
            $row = static::query()->orderBy('id')->first();
            if ($row) {
                return $row;
            }

            return static::create([
                'active' => true,
                'used_pages' => 0,
            ]);
        } catch (\Throwable $e) {
            return new static(['active' => true, 'used_pages' => 0]);
        }
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->copy()->endOfDay()->isPast();
    }

    public function quotaExhausted(): bool
    {
        if ($this->quota_pages === null) {
            return false;
        }

        return $this->used_pages >= $this->quota_pages;
    }

    public function isBlocked(): bool
    {
        return ! $this->active || $this->isExpired() || $this->quotaExhausted();
    }

    /** Pages left before quota is hit. null = unlimited. */
    public function remainingPages(): ?int
    {
        if ($this->quota_pages === null) {
            return null;
        }

        return max(0, $this->quota_pages - $this->used_pages);
    }

    /** Calendar days left until expiry. null = never expires. */
    public function remainingDays(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        $days = now()->startOfDay()->diffInDays($this->expires_at->copy()->startOfDay(), false);

        return max(0, (int) $days);
    }

    public function recordUsage(int $pages = 1): void
    {
        $this->increment('used_pages', $pages);
    }
}
