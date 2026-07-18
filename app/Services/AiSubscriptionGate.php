<?php

namespace App\Services;

use App\Models\AiSubscription;
use RuntimeException;

/**
 * Spec 007 — central enforcement gate for the AI subscription. Every AI call
 * (GeminiClient::extract / generateText) and every upload endpoint
 * (InvoiceController::store, LeaseController::store) checks assertAllowed()
 * before doing any work, so a blocked subscription (inactive, expired, or
 * quota-exhausted) stops extraction everywhere with one Arabic message.
 */
class AiSubscriptionGate
{
    /** @throws RuntimeException when the subscription is blocked. */
    public function assertAllowed(): void
    {
        if (AiSubscription::current()->isBlocked()) {
            throw new RuntimeException('انتهى اشتراك الذكاء الاصطناعي أو نفدت الحصة — يرجى تجديد الاشتراك');
        }
    }

    /** The current subscription row, for banner/status display. */
    public function check(): AiSubscription
    {
        return AiSubscription::current();
    }

    /**
     * Record N successfully-processed pages against the quota. Best-effort —
     * a usage-tracking failure must never break an extraction that already
     * succeeded (mirrors AuditLogger's swallow-on-failure philosophy).
     */
    public function recordPages(int $n = 1): void
    {
        try {
            AiSubscription::current()->recordUsage($n);
        } catch (\Throwable $e) {
            // best-effort; never abort the caller over usage tracking.
        }
    }
}
