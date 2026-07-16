<?php

namespace App\Console\Commands;

use App\Models\LeaseContract;
use App\Models\LeasePayment;
use App\Services\AlertDispatcher;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Spec 003 FR-204 — smart lease alerts. Scans lease_payments due dates (10 / 5 / 0
 * days before, and overdue) and lease_contracts end_date (30 / 15 / 0 days before)
 * and dispatches an alert (in-app + email + SMS, via AlertDispatcher) for each
 * window that is due today. Each payment/contract + window fires exactly once,
 * enforced by a stable `dedup_key` (AlertDispatcher skips a duplicate dedup_key).
 *
 * Reuses the expiry-bucket idea from Shop.php's municipal/health/commercial-license
 * CASE WHEN buckets (30/15/0-day windows), applied here to leases instead of shop
 * licenses.
 *
 * Register in app/Console/Kernel.php:
 *   $schedule->command('leases:scan-alerts')->dailyAt('06:00');
 */
class LeaseScanAlerts extends Command
{
    protected $signature = 'leases:scan-alerts';

    protected $description = 'Scan lease payment due dates and contract expiries and dispatch due/overdue/expiry alerts (Spec 003 FR-204)';

    /** Days-before-due-date windows for payment reminders (0 = due day). Overdue is any negative. */
    private const PAYMENT_WINDOWS = [10, 5, 0];

    /** Days-before-end-date windows for contract expiry reminders (0 = end day). */
    private const CONTRACT_WINDOWS = [30, 15, 0];

    public function handle(): int
    {
        $today = Carbon::today()->format('Y-m-d');

        $sent = $this->scanPayments($today) + $this->scanContracts($today);

        $this->info("Lease alert scan complete — {$sent} alert(s) dispatched.");

        return self::SUCCESS;
    }

    private function scanPayments(string $today): int
    {
        $sent = 0;

        LeasePayment::with('contract')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->chunkById(200, function ($payments) use ($today, &$sent) {
                foreach ($payments as $payment) {
                    $contract = $payment->contract;
                    if (! $contract || ! $contract->create_user) {
                        continue;
                    }

                    $window = self::matchedPaymentWindow(self::daysUntil($today, (string) $payment->due_date));
                    if ($window === null) {
                        continue;
                    }

                    $this->dispatchPaymentAlert($payment, $contract, $window);
                    $sent++;
                }
            });

        return $sent;
    }

    private function scanContracts(string $today): int
    {
        $sent = 0;

        LeaseContract::whereNotNull('end_date')
            ->chunkById(200, function ($contracts) use ($today, &$sent) {
                foreach ($contracts as $contract) {
                    if (! $contract->create_user) {
                        continue;
                    }

                    $window = self::matchedContractWindow(self::daysUntil($today, (string) $contract->end_date));
                    if ($window === null) {
                        continue;
                    }

                    $this->dispatchContractAlert($contract, $window);
                    $sent++;
                }
            });

        return $sent;
    }

    private function dispatchPaymentAlert(LeasePayment $payment, LeaseContract $contract, string $window): void
    {
        $title = $window === 'overdue' ? 'دفعة إيجار متأخرة السداد'
            : ($window === '0' ? 'دفعة إيجار مستحقة اليوم' : "دفعة إيجار مستحقة خلال {$window} أيام");

        $body = sprintf(
            'العقد %s — المستأجر %s — الدفعة رقم %d بقيمة %s ر.س، تاريخ الاستحقاق %s',
            $contract->contract_no ?: ('#'.$contract->id),
            $contract->tenant_name ?: '—',
            $payment->payment_no,
            number_format((float) $payment->amount, 2),
            (string) $payment->due_date
        );

        AlertDispatcher::send((int) $contract->create_user, 'lease_due', $title, $body, [
            'ref_type' => 'lease_payment',
            'ref_id' => $payment->id,
            'email' => true,
            'sms' => $window === 'overdue' || $window === '0',
            'dedup_key' => self::paymentDedupKey((int) $payment->id, $window),
        ]);
    }

    private function dispatchContractAlert(LeaseContract $contract, string $window): void
    {
        $title = $window === '0' ? 'عقد الإيجار ينتهي اليوم' : "عقد الإيجار ينتهي خلال {$window} يومًا";

        $body = sprintf(
            'العقد %s — المستأجر %s — ينتهي بتاريخ %s%s',
            $contract->contract_no ?: ('#'.$contract->id),
            $contract->tenant_name ?: '—',
            (string) $contract->end_date,
            $contract->renewal_terms ? ' — يوجد شرط تجديد، راجع الشروط' : ''
        );

        AlertDispatcher::send((int) $contract->create_user, 'lease_expiry', $title, $body, [
            'ref_type' => 'lease_contract',
            'ref_id' => $contract->id,
            'email' => true,
            'sms' => $window === '0',
            'dedup_key' => self::contractDedupKey((int) $contract->id, $window),
        ]);
    }

    // ----------------------------------------------------------------- pure logic

    /** Signed days between $today and $date: positive = future, negative = past (overdue). */
    public static function daysUntil(string $today, string $date): int
    {
        return (int) Carbon::parse($today)->diffInDays(Carbon::parse($date), false);
    }

    /** '10'|'5'|'0'|'overdue' if $daysUntil should fire a payment alert, else null. */
    public static function matchedPaymentWindow(int $daysUntil): ?string
    {
        if ($daysUntil < 0) {
            return 'overdue';
        }
        if (in_array($daysUntil, self::PAYMENT_WINDOWS, true)) {
            return (string) $daysUntil;
        }

        return null;
    }

    /** '30'|'15'|'0' if $daysUntil should fire a contract-expiry alert, else null. */
    public static function matchedContractWindow(int $daysUntil): ?string
    {
        return in_array($daysUntil, self::CONTRACT_WINDOWS, true) ? (string) $daysUntil : null;
    }

    /** Stable dedup key so a payment + window combination fires at most once. */
    public static function paymentDedupKey(int $paymentId, string $window): string
    {
        return "lease_payment:{$paymentId}:{$window}";
    }

    /** Stable dedup key so a contract + window combination fires at most once. */
    public static function contractDedupKey(int $contractId, string $window): string
    {
        return "lease_contract:{$contractId}:{$window}";
    }
}
