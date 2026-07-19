<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pushes AI-extracted invoices (isolated `invoices` connection) into the main
 * app's `purchase` table (default connection). This is the one deliberate place
 * the invoice subsystem crosses into the main schema.
 *
 * Mapping (see PurchaseController::store for the canonical insert):
 *   invoice_number       -> purchase_no      (required, UNIQUE in purchase)
 *   invoice_date         -> purchase_dt
 *   total_incl_vat       -> purchase_price   (the facture's total — chosen by the user)
 *   supplier_tax_number  -> tax_number
 *   supplier_name        -> purchase_respon
 *   image_path           -> purchasefile
 *   (user selection)     -> shop_id XOR manager_id  (required business rule)
 *   VAT breakdown        -> note             (purchase has no VAT columns)
 *
 * Only `status=done`, non-`needs_review`, not-already-mapped invoices with the
 * required fields are eligible. Each invoice is mapped independently so one bad
 * row never aborts the batch.
 */
class InvoicePurchaseMapper
{
    /**
     * Map a single invoice's raw attributes onto a `purchase` insert row.
     * Pure (no DB / container) so it is unit-testable without the main DB.
     */
    public static function buildPurchaseRow(array $a, ?int $shopId, ?int $managerId, int $userId): array
    {
        $date = $a['invoice_date'] ?? null;
        $date = $date ? substr((string) $date, 0, 10) : null; // normalise date / datetime -> Y-m-d

        $noteParts = [];
        if (filled($a['amount_before_vat'] ?? null)) {
            $noteParts[] = 'قبل الضريبة: '.$a['amount_before_vat'];
        }
        if (filled($a['vat_amount'] ?? null)) {
            $noteParts[] = 'ضريبة: '.$a['vat_amount'];
        }
        $noteParts[] = 'مُرحّل آلياً من استخراج الفواتير (دفعة #'.($a['batch_id'] ?? '?').' صفحة '.($a['page_number'] ?? '?').')';

        $dueDate = ($a['due_date'] ?? null) ? substr((string) $a['due_date'], 0, 10) : null;

        return [
            'purchase_no' => trim((string) ($a['invoice_number'] ?? '')),
            'purchase_price' => $a['total_incl_vat'] ?? null,
            'purchase_dt' => $date,
            'tax_number' => $a['supplier_tax_number'] ?? null,
            'purchase_respon' => $a['supplier_name'] ?? null,
            'shop_id' => $shopId,
            'manager_id' => $managerId,
            'purchasefile' => $a['image_path'] ?? null,
            'note' => implode(' | ', $noteParts),
            'create_user' => $userId,
            // Spec 002 — full invoice data now has dedicated columns (additive; nullable).
            'amount_before_vat' => $a['amount_before_vat'] ?? null,
            'vat_amount' => $a['vat_amount'] ?? null,
            'vat_rate' => $a['vat_rate'] ?? null,
            'discount_total' => $a['discount_total'] ?? null,
            'currency' => $a['currency'] ?? null,
            'invoice_type' => $a['invoice_type'] ?? null,
            'payment_method' => $a['payment_method'] ?? null,
            'commercial_registration' => $a['commercial_registration'] ?? null,
            'due_date' => $dueDate,
            'source' => 'ai',
        ];
    }

    /**
     * Decide how to insert a row into `purchase_attach` (المرفقات) given its real
     * column list — there is no migration/insert in the codebase to copy, so we
     * adapt to whatever the prod schema actually has. Returns the FK + file
     * column names (+ which optional columns exist), or null if undetectable.
     * Pure so it can be unit-tested without the remote DB.
     */
    public static function detectAttachColumns(array $columns): ?array
    {
        $cols = array_map('strtolower', $columns);
        $has = fn ($c) => in_array($c, $cols, true);

        $fk = $has('purchase_id') ? 'purchase_id' : null;

        $file = null;
        foreach (['attach_url', 'purchase_attach_url', 'file_url', 'attachfile', 'purchasefile', 'file', 'url', 'path', 'attach', 'document', 'doc_url'] as $cand) {
            if ($has($cand)) {
                $file = $cand;
                break;
            }
        }

        if (! $fk || ! $file) {
            return null;
        }

        return [
            'fk' => $fk,
            'file' => $file,
            'create_user' => $has('create_user'),
            'created_at' => $has('created_at'),
            'type' => $has('type'),
        ];
    }

    /**
     * Best-effort: also add the invoice image to the purchase's attachments list
     * (المرفقات / purchase_attach). Never throws — a failed attach must not undo
     * the purchase that was already created. Records the outcome in $summary.
     */
    private function attachToPurchase(int $purchaseId, ?string $fileUrl, int $userId, array &$summary): void
    {
        if (! $fileUrl) {
            return;
        }
        try {
            if (! Schema::hasTable('purchase_attach')) {
                return;
            }
            $map = self::detectAttachColumns(Schema::getColumnListing('purchase_attach'));
            if (! $map) {
                $summary['attach_skipped']++;

                return;
            }

            $row = [$map['fk'] => $purchaseId, $map['file'] => $fileUrl];
            if ($map['create_user']) {
                $row['create_user'] = $userId;
            }
            if ($map['created_at']) {
                $row['created_at'] = now();
            }
            if ($map['type']) {
                $row['type'] = 'invoice';
            }

            DB::table('purchase_attach')->insert($row);
            $summary['attached']++;
        } catch (\Throwable $e) {
            $summary['attach_errors'][] = $e->getMessage();
        }
    }

    /** Is this invoice (raw attributes) safe to push to a purchase row? */
    public static function isEligible(array $a): bool
    {
        return ($a['status'] ?? null) === 'done'
            && (int) ($a['needs_review'] ?? 0) !== 1
            && ! filled($a['purchase_id'] ?? null)
            && filled($a['invoice_number'] ?? null)
            && filled($a['invoice_date'] ?? null)
            && filled($a['total_incl_vat'] ?? null);
    }

    /**
     * Push every eligible invoice of $batch into `purchase`, assigning the given
     * shop XOR manager. Returns a per-outcome summary.
     *
     * @param  array<int>|bool  $dupOverride  Invoice IDs the human confirmed as
     *         NOT duplicates (per-invoice fuzzy-block override). `true` = override
     *         for the whole batch (explicit escape hatch, audited per invoice).
     */
    public function push(InvoiceBatch $batch, ?int $shopId, ?int $managerId, int $userId, array|bool $dupOverride = []): array
    {
        $shopId = $shopId ?: null;
        $managerId = $managerId ?: null;
        if ((! $shopId && ! $managerId) || ($shopId && $managerId)) {
            throw new \InvalidArgumentException('اختر قائد مجموعة أو محل (واحد فقط)');
        }

        // Fresh supplier master per batch push — the static cache in SupplierMatcher
        // would otherwise go stale inside long-running queue workers.
        SupplierMatcher::flushCache();

        $summary = [
            'pushed' => 0,
            'pushed_ids' => [],
            'duplicates' => [],
            'fuzzy_duplicates' => [],
            'already_mapped' => 0,
            'ineligible' => 0,
            'errors' => [],
            'attached' => 0,
            'attach_skipped' => 0,
            'attach_errors' => [],
        ];

        foreach ($batch->invoices()->orderBy('page_number')->get() as $inv) {
            $a = $inv->getAttributes(); // raw, uncast values — matches buildPurchaseRow's contract

            if (filled($a['purchase_id'] ?? null)) {
                $summary['already_mapped']++;

                continue;
            }
            if (! self::isEligible($a)) {
                $summary['ineligible']++;

                continue;
            }

            $no = trim((string) ($a['invoice_number'] ?? ''));
            try {
                // purchase_no is UNIQUE; skip anything that already exists in the main schema.
                if (DB::table('purchase')->where('purchase_no', $no)->exists()) {
                    $summary['duplicates'][] = $no;

                    continue;
                }

                // Spec 002 FR-106 — fuzzy/file-hash duplicate against earlier invoices.
                // Suspected duplicates are NOT auto-created; they're surfaced for a human
                // decision. When THIS invoice's id is in $dupOverride the caller has
                // confirmed it specifically, so skip the fuzzy block (exact purchase_no
                // uniqueness is still enforced by the DB).
                $overrideThis = $dupOverride === true || in_array((int) $inv->id, array_map('intval', (array) $dupOverride), true);
                if (! $overrideThis) {
                    $dup = (new DuplicateDetector())->findDuplicate($a, (int) ($a['batch_id'] ?? 0));
                    if ($dup) {
                        $summary['fuzzy_duplicates'][] = [
                            'invoice_number' => $no,
                            'matches_invoice_id' => $dup['invoice']->id,
                            'score' => $dup['score'],
                            'reason' => 'تشابه مرتفع مع فاتورة سابقة (#'.$dup['invoice']->id.')',
                        ];

                        continue;
                    }
                } else {
                    \App\Services\AuditLogger::log('invoice', (int) $inv->id, \App\Services\AuditLogger::DUP_OVERRIDE, [
                        'batch_id' => $inv->batch_id,
                        'note' => 'تم تجاوز التحقق من التكرار وترحيل الفاتورة إلى المشتريات',
                    ]);
                }

                $row = self::buildPurchaseRow($a, $shopId, $managerId, $userId);
                $row['supplier_id'] = $this->resolveSupplierId($a, $userId);   // Spec 002 FR-105
                $row['created_at'] = now();

                // Keep the purchase insert, line items, and invoice mapping atomic so a
                // failure in copyLineItems() cannot leave an orphan purchase row that
                // permanently blocks this invoice as a "duplicate" purchase_no.
                $purchaseId = DB::transaction(function () use ($row, $inv) {
                    $purchaseId = DB::table('purchase')->insertGetId($row);

                    // Copy extracted line items -> purchase_items (Spec 002 FR-102).
                    $this->copyLineItems($inv, $purchaseId);

                    // Record the link on the isolated side so re-pushing is idempotent.
                    $inv->forceFill(['purchase_id' => $purchaseId, 'mapped_at' => now()])->save();

                    return $purchaseId;
                });

                // Spec 001 FR-006 — audit the approval (invoice -> purchase).
                // Kept outside the transaction: a logging failure must not roll back the
                // purchase that was already created.
                \App\Services\AuditLogger::log('invoice', (int) $inv->id, \App\Services\AuditLogger::APPROVE, [
                    'batch_id' => $inv->batch_id,
                    'note' => 'مُرحّلة إلى المشتريات #'.$purchaseId,
                ]);

                // Also add the invoice image to the purchase's attachments (المرفقات).
                $this->attachToPurchase($purchaseId, $a['image_path'] ?? null, $userId, $summary);

                $summary['pushed']++;
                $summary['pushed_ids'][] = $purchaseId;
            } catch (\Illuminate\Database\QueryException $qe) {
                // Belt-and-suspenders: the exists() check above can lose a race with a
                // concurrent push (or a manual insert) between check and insert. If the
                // DB rejects the row as a duplicate key on purchase_no, classify it as a
                // duplicate (blocked) rather than a generic error.
                if (self::isDuplicateKeyViolation($qe)) {
                    $summary['duplicates'][] = $no;
                } else {
                    $summary['errors'][] = ['invoice_number' => $no, 'message' => $qe->getMessage()];
                }
            } catch (\Throwable $e) {
                $summary['errors'][] = ['invoice_number' => $no, 'message' => $e->getMessage()];
            }
        }

        return $summary;
    }

    /** True when a QueryException is a unique/duplicate-key violation (any driver). */
    public static function isDuplicateKeyViolation(\Illuminate\Database\QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (string) ($e->errorInfo[1] ?? '');
        $msg = strtolower($e->getMessage());

        return $sqlState === '23000'              // integrity constraint violation (MySQL/others)
            || $sqlState === '23505'              // unique_violation (Postgres)
            || $driverCode === '1062'             // MySQL ER_DUP_ENTRY
            || $driverCode === '1'                // SQLite constraint
            || str_contains($msg, 'duplicate')
            || str_contains($msg, 'unique constraint');
    }

    /**
     * Resolve the supplier for an extracted invoice against the suppliers master,
     * creating a new supplier when there's no confident match (Spec 002 FR-105).
     * Returns the supplier id, or null when there's nothing to match on.
     */
    private function resolveSupplierId(array $a, int $userId): ?int
    {
        $name = trim((string) ($a['supplier_name'] ?? ''));
        $tax = preg_replace('/\D+/', '', (string) ($a['supplier_tax_number'] ?? ''));
        if ($name === '' && $tax === '') {
            return null;
        }

        $result = (new SupplierMatcher())->match($tax ?: null, $name ?: null);
        if ($result['match']) {
            return (int) $result['match']->id;
        }

        return (int) \App\Models\Supplier::create([
            'name' => $name ?: null,
            'tax_number' => $tax ?: null,
            'cr_number' => $a['commercial_registration'] ?? null,
            'create_user' => $userId,
        ])->id;
    }

    /** Copy an extracted invoice's line items into purchase_items (Spec 002 FR-102). */
    private function copyLineItems(Invoice $inv, int $purchaseId): void
    {
        $items = \App\Models\InvoiceItem::on($inv->getConnectionName())
            ->where('invoice_id', $inv->id)->orderBy('line_no')->get();

        foreach ($items as $it) {
            DB::table('purchase_items')->insert([
                'purchase_id' => $purchaseId,
                'line_no' => $it->line_no,
                'name' => $it->name,
                'quantity' => $it->quantity,
                'unit' => $it->unit,
                'unit_price' => $it->unit_price,
                'line_total' => $it->line_total,
                'vat_rate' => $it->vat_rate,
                'vat_amount' => $it->vat_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** Count invoices in $batch that would be pushed right now (for the UI). */
    public function eligibleCount(InvoiceBatch $batch): int
    {
        return $batch->invoices()->get()
            ->filter(fn (Invoice $i) => self::isEligible($i->getAttributes()))
            ->count();
    }
}
