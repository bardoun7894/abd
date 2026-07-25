<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spec 024 F2 follow-up — builds the lease context printed on a سند قبض for one
 * `shop_rentpay` installment: اسم المحل، رقم العقد، رقم الدفعة (n من m)،
 * والفترة المستحقة (من → إلى).
 *
 * Extracted out of ShopController::rentpayReceipt() because the first F2 pass
 * got three of these wrong and they are only meaningfully testable in
 * isolation:
 *
 *  - payment_no was the raw `rentpay_id` primary key, not the installment's
 *    ordinal within its contract. It is now the 1-based rank of the row among
 *    its shop's installments ordered by (rentpay_dt, rentpay_id), with the
 *    count carried separately in payment_total so the voucher prints "3 من 12".
 *  - period_from/period_to were the WHOLE contract span, identical on every
 *    voucher for that shop. They are now the span that THIS installment covers.
 *  - the contract was picked with ->first() — an arbitrary row when a shop has
 *    more than one lease. It is now the contract whose [rent_sdt, rent_edt]
 *    window contains the installment's due date, falling back to the most
 *    recent contract.
 *
 * INSTALLMENT-PERIOD CONVENTION: `rentpay_dt` is the due date and
 * LeaseScheduleGenerator sets due_1 = contract start, so installment i covers
 * [due_i, due_(i+1) − 1 day], and the last one runs to the contract end date.
 * Hand-entered rows (updrentpay) can violate that ordering, so any degenerate
 * result — a single installment, missing dates, or from > to — falls back to
 * the contract span rather than printing nonsense on a financial document.
 *
 * Every lookup is Schema-guarded: on an environment/test schema that predates
 * the F2 migrations (or lacks the legacy shop/shop_rent tables) the missing
 * keys are simply dropped, and the caller records the سند exactly as before.
 */
class RentpayVoucherContext
{
    /** cash_receipt columns this service can populate, if they exist. */
    private const OPTIONAL_COLUMNS = [
        'shop_name', 'contract_no', 'payment_no', 'payment_total', 'period_from', 'period_to',
    ];

    /**
     * @param  object  $rentpay  a `shop_rentpay` row (needs rentpay_id + shop_id)
     * @return array<string,string|null> only the keys whose cash_receipt column exists
     */
    public function build($rentpay): array
    {
        $shopId = $rentpay->shop_id ?? null;
        $rentpayId = (int) ($rentpay->rentpay_id ?? 0);

        $contract = $this->resolveContract($shopId, $rentpay->rentpay_dt ?? null);
        [$paymentNo, $paymentTotal, $periodFrom, $periodTo] = $this->resolveInstallment($shopId, $rentpayId, $contract);

        $context = [
            'shop_name' => $this->resolveShopName($shopId),
            'contract_no' => $contract->rent_no ?? $contract->rent_name ?? null,
            'payment_no' => $paymentNo,
            'payment_total' => $paymentTotal,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
        ];

        // Drop anything the current cash_receipt schema cannot store — passing an
        // unknown key straight through to an insert would throw.
        return array_intersect_key($context, array_flip($this->writableColumns()));
    }

    /** اسم المحل — snapshotted so a later rename never rewrites issued vouchers. */
    private function resolveShopName($shopId): ?string
    {
        if (! $shopId || ! Schema::hasTable('shop')) {
            return null;
        }

        return DB::table('shop')->where('shop_id', $shopId)->value('shop_name');
    }

    /**
     * The lease whose window contains the installment's due date; otherwise the
     * most recent contract for the shop. Never an arbitrary ->first().
     */
    private function resolveContract($shopId, $dueDate)
    {
        if (! $shopId || ! Schema::hasTable('shop_rent')) {
            return null;
        }

        $contracts = DB::table('shop_rent')->where('shop_id', $shopId)->get();
        if ($contracts->isEmpty()) {
            return null;
        }

        $due = $this->toDate($dueDate);
        if ($due) {
            foreach ($contracts as $contract) {
                $start = $this->toDate($contract->rent_sdt ?? null);
                $end = $this->toDate($contract->rent_edt ?? null);
                if ($start && $end && $due->between($start, $end)) {
                    return $contract;
                }
            }
        }

        return $contracts->sortByDesc(fn ($c) => $this->toDate($c->rent_sdt ?? null)?->timestamp ?? 0)->first();
    }

    /**
     * @return array{0:?string,1:?string,2:?string,3:?string} [payment_no, payment_total, period_from, period_to]
     */
    private function resolveInstallment($shopId, int $rentpayId, $contract): array
    {
        $contractFrom = $this->toDate($contract->rent_sdt ?? null)?->toDateString();
        $contractTo = $this->toDate($contract->rent_edt ?? null)?->toDateString();

        if (! $shopId || ! Schema::hasTable('shop_rentpay')) {
            return [null, null, $contractFrom, $contractTo];
        }

        $siblings = DB::table('shop_rentpay')
            ->where('shop_id', $shopId)
            ->orderBy('rentpay_dt')
            ->orderBy('rentpay_id')
            ->get(['rentpay_id', 'rentpay_dt'])
            ->values();

        $total = $siblings->count();
        $index = $siblings->search(fn ($s) => (int) $s->rentpay_id === $rentpayId);
        if ($index === false) {
            return [null, $total ? (string) $total : null, $contractFrom, $contractTo];
        }

        $paymentNo = (string) ($index + 1);
        $paymentTotal = (string) $total;

        // A lone installment covers the whole contract by definition.
        if ($total < 2) {
            return [$paymentNo, $paymentTotal, $contractFrom, $contractTo];
        }

        $from = $this->toDate($siblings[$index]->rentpay_dt ?? null);
        $next = $siblings[$index + 1] ?? null;
        $to = $next
            ? $this->toDate($next->rentpay_dt ?? null)?->subDay()
            : $this->toDate($contract->rent_edt ?? null);

        // Hand-entered rows can be unordered or undated — never print an
        // inverted or half-empty period on a financial document.
        if (! $from || ! $to || $from->greaterThan($to)) {
            return [$paymentNo, $paymentTotal, $contractFrom, $contractTo];
        }

        return [$paymentNo, $paymentTotal, $from->toDateString(), $to->toDateString()];
    }

    private function toDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return array<int,string> */
    private function writableColumns(): array
    {
        if (! Schema::hasTable('cash_receipt')) {
            return [];
        }

        return array_values(array_filter(
            self::OPTIONAL_COLUMNS,
            fn ($col) => Schema::hasColumn('cash_receipt', $col)
        ));
    }
}
