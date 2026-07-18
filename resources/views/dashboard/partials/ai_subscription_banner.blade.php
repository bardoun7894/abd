{{--
    Spec 007 — AI subscription "remaining quota" banner. Included ONLY on the
    invoice and lease upload screens. Expects a $subscription
    (App\Models\AiSubscription) variable from the controller.
--}}
@if (isset($subscription))
    @php
        $blocked = $subscription->isBlocked();
        $remainingPages = $subscription->remainingPages();
        $remainingDays = $subscription->remainingDays();

        // Amber ("low") thresholds — purely cosmetic, doesn't affect enforcement.
        $lowPages = $remainingPages !== null && $remainingPages <= 10;
        $lowDays = $remainingDays !== null && $remainingDays <= 7;

        $variant = $blocked ? 'danger' : (($lowPages || $lowDays) ? 'warning' : 'success');
        $icon = $blocked ? 'fa-ban' : (($lowPages || $lowDays) ? 'fa-exclamation-triangle' : 'fa-check-circle');
    @endphp
    <div class="alert alert-{{ $variant }} d-flex align-items-center mb-5" role="alert">
        <i class="fa {{ $icon }} me-2"></i>
        <div class="d-flex flex-column">
            @if ($blocked)
                <span class="fw-bold">انتهى اشتراك الذكاء الاصطناعي أو نفدت الحصة — يرجى تجديد الاشتراك من الإعدادات.</span>
            @else
                <span class="fw-bold">
                    الصفحات المتبقية:
                    {{ $remainingPages === null ? 'غير محدودة' : number_format($remainingPages) }}
                    @if ($remainingDays !== null)
                        — الأيام المتبقية للاشتراك: {{ number_format($remainingDays) }}
                    @endif
                </span>
                @if ($subscription->quota_pages !== null)
                    <small class="text-muted">المستخدم: {{ number_format($subscription->used_pages) }} / {{ number_format($subscription->quota_pages) }} صفحة</small>
                @endif
            @endif
        </div>
    </div>
@endif
