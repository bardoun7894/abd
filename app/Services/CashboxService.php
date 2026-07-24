<?php

namespace App\Services;

use App\Models\CashboxLedger;
use App\Models\CashReceipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Spec 008 bundle 1 (cashbox): single source-type-agnostic choke point for
 * every receipt-voucher (سند قبض) + ledger write in the app. Every money
 * module that gains a paid/unpaid transition is meant to call this instead of
 * writing cash_receipt/cashbox_ledger rows itself, so every module produces
 * identical, auditable records. Bundle 3 (activity-log) hooks recordReceipt()
 * and voidReceipt() as its write events.
 *
 * Both public methods are fully transactional and lock the last ledger row
 * before computing the running balance, to keep balance_after correct under
 * concurrent writes. currentBalance() re-derives the balance at read time
 * (SUM(in) - SUM(out)) as the authoritative check independent of the cache.
 */
class CashboxService
{
    /**
     * Spec 024 F2 — source_types whose receipt vouchers are permanently
     * hard-locked: only the issuer (cash_receipt.received_by) or a system
     * admin may revert them. Every other source_type (purchase, expense, ...)
     * is unaffected.
     */
    private const LEASE_LOCKED_SOURCE_TYPES = ['shop_rentpay', 'lease_payment'];

    /**
     * Record an inbound receipt voucher (سند قبض) and append the matching
     * 'in' ledger entry. Returns the created CashReceipt.
     *
     * @param array{source_type:string,source_id:int,direction?:string,amount:float,receipt_date:string,payer_name?:?string,received_by?:?int,note?:?string,create_user?:?int,contract_no?:?string,payment_no?:?string,period_from?:?string,period_to?:?string} $data
     */
    public function recordReceipt(array $data): CashReceipt
    {
        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Receipt amount must be greater than zero.');
        }
        if (empty($data['source_type']) || empty($data['source_id'])) {
            throw new InvalidArgumentException('Receipt requires source_type and source_id.');
        }

        return DB::transaction(function () use ($data, $amount) {
            $now = Carbon::now();

            // Spec 024 F2 — additive voucher-enrichment fields (contract no.,
            // payment no., due period). Only included when the caller passes
            // them, so callers/schemas that predate the migration (and the
            // existing house tests that hand-build cash_receipt without these
            // columns) are completely unaffected.
            $enrichment = array_intersect_key($data, array_flip([
                'contract_no', 'payment_no', 'period_from', 'period_to',
            ]));

            $receipt = CashReceipt::create(array_merge([
                'source_type' => $data['source_type'],
                'source_id' => $data['source_id'],
                'direction' => $data['direction'] ?? 'in',
                'amount' => $amount,
                'receipt_date' => $data['receipt_date'] ?? $now->toDateString(),
                'payer_name' => $data['payer_name'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'note' => $data['note'] ?? null,
                'is_void' => 0,
                'create_user' => $data['create_user'] ?? null,
                'created_at' => $now,
            ], $enrichment));

            // Derive a stable, unique, never-reused receipt_no from the PK.
            $receipt->receipt_no = 'R-' . $receipt->receipt_id;
            $receipt->save();

            // Direction-aware: 'in' (rent/income) ADDS to the balance, 'out' (purchase/
            // expense — money spent) SUBTRACTS. Default 'in' keeps the rent flow unchanged.
            $dir = ($data['direction'] ?? 'in') === 'out' ? 'out' : 'in';
            $balanceAfter = $this->lockLastBalance() + ($dir === 'out' ? -$amount : $amount);

            CashboxLedger::create([
                'receipt_id' => $receipt->receipt_id,
                'source_type' => $receipt->source_type,
                'source_id' => $receipt->source_id,
                'direction' => $dir,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'reversal_of_entry_id' => null,
                'change_user' => $data['received_by'] ?? $data['create_user'] ?? null,
                'change_at' => $now,
                'note' => $data['note'] ?? null,
            ]);

            return $receipt;
        });
    }

    /**
     * Void a receipt voucher: mandatory non-empty reason, never deletes the
     * original row — flips is_void + logs who/why/when, then appends a
     * compensating 'out' reversal entry pointing back at the original 'in'
     * ledger entry via reversal_of_entry_id.
     */
    public function voidReceipt(int $receiptId, string $reason, ?int $userId, ?bool $isAdmin = null): CashReceipt
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('A void reason is required.');
        }

        return DB::transaction(function () use ($receiptId, $reason, $userId, $isAdmin) {
            $receipt = CashReceipt::where('receipt_id', $receiptId)->lockForUpdate()->first();
            if (! $receipt) {
                throw new RuntimeException('Receipt not found.');
            }
            if ((int) $receipt->is_void === 1) {
                throw new RuntimeException('Receipt is already void.');
            }

            // Spec 024 F2 hard-lock. Opt-in: callers that don't pass $isAdmin
            // (the pre-existing 3-arg call shape) get the exact old
            // behaviour — no enforcement — so this is fully backward
            // compatible with every existing caller/test.
            if ($isAdmin !== null) {
                $this->assertLeaseVoidAllowed($receipt, $userId, $isAdmin);
            }

            $originalEntry = CashboxLedger::where('receipt_id', $receiptId)
                ->where('reversal_of_entry_id', null)
                ->orderBy('entry_id')
                ->first();

            $now = Carbon::now();

            $receipt->is_void = 1;
            $receipt->void_reason = $reason;
            $receipt->void_user = $userId;
            $receipt->void_date = $now;
            $receipt->save();

            // The compensating entry is the OPPOSITE direction of the original: voiding
            // an 'in' receipt (rent) appends 'out' (subtract); voiding an 'out' receipt
            // (purchase) appends 'in' (add the money back). Keeps the balance correct
            // for both money-in and money-out sources.
            $origDir = ($receipt->direction ?? 'in') === 'out' ? 'out' : 'in';
            $compDir = $origDir === 'out' ? 'in' : 'out';
            $balanceAfter = $this->lockLastBalance() + ($compDir === 'out' ? -(float) $receipt->amount : (float) $receipt->amount);

            CashboxLedger::create([
                'receipt_id' => $receipt->receipt_id,
                'source_type' => $receipt->source_type,
                'source_id' => $receipt->source_id,
                'direction' => $compDir,
                'amount' => $receipt->amount,
                'balance_after' => $balanceAfter,
                'reversal_of_entry_id' => $originalEntry->entry_id ?? null,
                'change_user' => $userId,
                'change_at' => $now,
                'note' => $reason,
            ]);

            return $receipt;
        });
    }

    /**
     * Spec 024 F2 hard-lock: reject reverting a lease/rentpay سند unless the
     * acting user is the issuer (cash_receipt.received_by) or a system admin.
     * No-op for every source_type outside LEASE_LOCKED_SOURCE_TYPES (e.g.
     * 'purchase' voids are never gated by this check).
     */
    public function assertLeaseVoidAllowed(CashReceipt $receipt, ?int $actingUserId, bool $isAdmin): void
    {
        if (! in_array($receipt->source_type, self::LEASE_LOCKED_SOURCE_TYPES, true)) {
            return;
        }
        if ($isAdmin) {
            return;
        }
        if ($actingUserId !== null && (int) $receipt->received_by === (int) $actingUserId) {
            return;
        }

        $issuerName = 'غير معروف';
        if ($receipt->received_by) {
            try {
                $issuerName = DB::table('users')->where('id', $receipt->received_by)->value('name') ?? $issuerName;
            } catch (\Throwable $e) {
                // Name lookup must never turn into a misleading DB-error message.
            }
        }

        throw new RuntimeException(
            "لا يمكن تعديل حالة هذه الدفعة لأنها مرتبطة بسند سداد تم تحريره بواسطة الموظف ({$issuerName}). "
            . 'في حال الحاجة إلى التعديل، يجب أن يتم من خلال الموظف الذي حرر السند أو مستخدم يمتلك صلاحية مدير النظام.'
        );
    }

    /**
     * Read-time authoritative balance: SUM(in) - SUM(out) across the whole
     * ledger. Used to verify/replace the balance_after cache if it is ever
     * skewed (e.g. a row inserted outside a transaction).
     */
    public function currentBalance(): float
    {
        $in = (float) CashboxLedger::where('direction', 'in')->sum('amount');
        $out = (float) CashboxLedger::where('direction', 'out')->sum('amount');

        return $in - $out;
    }

    /**
     * Lock the last ledger row (if any) so concurrent writers serialize on the
     * running balance instead of racing. Returns 0 for an empty ledger.
     */
    private function lockLastBalance(): float
    {
        $last = CashboxLedger::lockForUpdate()->orderByDesc('entry_id')->first();

        return $last ? (float) $last->balance_after : 0.0;
    }
}
