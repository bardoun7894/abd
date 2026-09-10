<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        /*
         * purchase.note carries the VAT breakdown and NOTHING about where the row
         * came from.
         *
         * It used to end with a part naming the extraction batch and page. The
         * client's standing instruction (2026-07-26, the third time they raised
         * it — after the دفعات note and the سند footer) is that a record must not
         * advertise that a machine produced it: "اجعلها حالها من حال مدخلات
         * الموظف". This note is shown verbatim on the المشتريات screen, so that
         * part announced the automatic posting to everyone who opened the list.
         *
         * The link back to the extraction is NOT lost — AuditLogger records the
         * invoice→purchase approval with the batch id, and the invoice row itself
         * keeps its own mapping columns. Only the human-facing note changed.
         */
        $noteParts = [];
        if (filled($a['amount_before_vat'] ?? null)) {
            $noteParts[] = 'قبل الضريبة: '.$a['amount_before_vat'];
        }
        if (filled($a['vat_amount'] ?? null)) {
            $noteParts[] = 'ضريبة: '.$a['vat_amount'];
        }

        $dueDate = ($a['due_date'] ?? null) ? substr((string) $a['due_date'], 0, 10) : null;

        return [
            'purchase_no' => trim((string) ($a['invoice_number'] ?? '')),
            'purchase_price' => $a['total_incl_vat'] ?? null,
            'purchase_dt' => $date,
            'tax_number' => $a['supplier_tax_number'] ?? null,
            'purchase_respon' => self::canonicalSupplierName(
                $a['supplier_tax_number'] ?? null,
                $a['commercial_registration'] ?? null,
                $a['supplier_name'] ?? null
            ),
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
     * Copy an AI invoice page image from the invoices-module public dir into the
     * purchases' own public dir (uploads/users/images) — the same location a manual
     * purchase attachment lives — and return the new relative path. This decouples the
     * posted purchase's attachment from the invoices storage so it keeps working even
     * if the invoice batch is later deleted. Fail-open: returns the original path on any
     * problem (that path is still served via asset() after the viewer fix).
     */
    public static function copyImageToPurchases(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return $imagePath;
        }
        try {
            $parts = explode('#', $imagePath, 2);
            $clean = $parts[0];
            $fragment = $parts[1] ?? null;
            $src = public_path($clean);
            if (! is_file($src)) {
                return $imagePath;
            }
            $ext = pathinfo($clean, PATHINFO_EXTENSION) ?: 'png';
            $destDir = public_path('uploads/users/images');
            if (! is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            $name = 'inv_'.\Illuminate\Support\Str::random(12).'.'.$ext;

            // Whole-document fallback paths carry "#page=K". Copying the WHOLE
            // source.pdf made every purchase attachment open at page 1 — «أي
            // فاتورة أضغط يجي فاتورة رقم واحد» (sabah, 2026-08-14). Best: extract
            // just page K with FPDI (pure PHP — exec() is disabled on the prod
            // host) so the attachment IS the invoice's own page. Some sources use
            // compressed xref streams the free FPDI parser rejects (the same
            // reason the pipeline fell back to whole-document mode); then copy
            // the whole file but KEEP the fragment on the stored path so the
            // browser's own PDF viewer still jumps to page K.
            $pageFragment = null;
            if ($fragment && preg_match('/^page=(\d+)$/', $fragment, $m)) {
                $pageFragment = '#page='.$m[1];
                if (strtolower($ext) === 'pdf') {
                    try {
                        (new PdfPageSplitter())->extractPage($src, (int) $m[1], $destDir.'/'.$name);

                        return 'uploads/users/images/'.$name;
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('copyImageToPurchases: page extraction failed, copying whole file with fragment', [
                            'image_path' => $imagePath,
                            'reason' => $e->getMessage(),
                        ]);
                    }
                }
            }

            if (@copy($src, $destDir.'/'.$name)) {
                return 'uploads/users/images/'.$name.$pageFragment;
            }
        } catch (\Throwable $e) {
            // fall through to the original path
        }

        return $imagePath;
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
    private function attachToPurchase(int $purchaseId, ?string $fileUrl, int $userId, bool $tableExists, ?array $map, array &$summary): void
    {
        if (! $fileUrl || ! $tableExists) {
            return;
        }
        if (! $map) {
            $summary['attach_skipped']++;

            return;
        }
        try {
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
     * Human-readable Arabic reason this invoice (raw attributes) cannot be pushed,
     * or null when it IS eligible. Mirrors isEligible() exactly and is pure over the
     * raw attribute array. Multiple blockers are composed (·-joined) in the same
     * order isEligible() checks them, so the user sees every reason at once.
     */
    public static function ineligibilityReason(array $a): ?string
    {
        if (self::isEligible($a)) {
            return null;
        }

        // Already mapped is the "posted" state, not a blocker to surface.
        if (filled($a['purchase_id'] ?? null)) {
            return null;
        }

        $reasons = [];
        if (($a['status'] ?? null) !== 'done') {
            $reasons[] = 'لم يكتمل الاستخراج';
        }
        if ((int) ($a['needs_review'] ?? 0) === 1) {
            $reasons[] = 'بحاجة مراجعة';
        }
        if (! filled($a['invoice_number'] ?? null)) {
            $reasons[] = 'رقم الفاتورة مفقود';
        }
        if (! filled($a['invoice_date'] ?? null)) {
            $reasons[] = 'التاريخ مفقود';
        }
        if (! filled($a['total_incl_vat'] ?? null)) {
            $reasons[] = 'الإجمالي مفقود';
        }

        return $reasons ? implode(' · ', $reasons) : null;
    }

    /**
     * Push every eligible invoice of $batch into `purchase`, assigning the given
     * shop XOR manager. Returns a per-outcome summary.
     *
     * @param  array<int>|bool  $dupOverride  Invoice IDs the human confirmed as
     *         NOT duplicates (per-invoice fuzzy-block override). `true` = override
     *         for the whole batch (explicit escape hatch, audited per invoice).
     * @param  ?array<int>  $onlyInvoiceIds  Spec 024 — when non-null, restrict this
     *         push to exactly these invoice ids within the batch (per-invoice
     *         checkbox posting). All existing callers pass nothing/null, which keeps
     *         the whole-batch behaviour identical to before this param existed.
     */
    public function push(InvoiceBatch $batch, ?int $shopId, ?int $managerId, int $userId, array|bool $dupOverride = [], ?array $onlyInvoiceIds = null): array
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
            'ineligible_details' => [],
            'errors' => [],
            'attached' => 0,
            'attach_skipped' => 0,
            'attach_errors' => [],
            'link_errors' => [], // purchase committed, sqlite link write failed -> needs manual reconcile
        ];

        // C3 — introspect purchase_attach schema ONCE per push (was per-invoice in attachToPurchase()).
        $attachTableExists = false;
        $attachMap = null;
        try {
            $attachTableExists = Schema::hasTable('purchase_attach');
            if ($attachTableExists) {
                $attachMap = self::detectAttachColumns(Schema::getColumnListing('purchase_attach'));
            }
        } catch (\Throwable $e) {
            $attachTableExists = false;
            $attachMap = null;
        }

        // C2 — memory-bounded chunking for large batches; synchronous summary contract unchanged.
        // Row count of the matched set is invariant (we set purchase_id but never remove rows),
        // so plain offset chunk() is offset-stable here.
        $batch->invoices()
            ->when($onlyInvoiceIds !== null, fn ($q) => $q->whereIn('id', $onlyInvoiceIds))
            ->orderBy('page_number')
            ->chunk(100, function ($invoices) use ($shopId, $managerId, $userId, $dupOverride, $attachTableExists, $attachMap, &$summary) {
        foreach ($invoices as $inv) {
            $a = $inv->getAttributes(); // raw, uncast values — matches buildPurchaseRow's contract

            if (filled($a['purchase_id'] ?? null)) {
                $summary['already_mapped']++;

                continue;
            }
            if (! self::isEligible($a)) {
                $summary['ineligible']++;
                $summary['ineligible_details'][] = [
                    'id' => (int) $inv->id,
                    'invoice_number' => $a['invoice_number'] ?? null,
                    'reason' => self::ineligibilityReason($a),
                ];

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

                // Copy the invoice page image into the purchases' own public dir so the
                // attachment is decoupled from the invoices-module storage (survives a
                // later batch delete) and is served by the exact public path a manual
                // purchase attachment uses. Fail-open: on any issue keep the original path.
                $a['image_path'] = self::copyImageToPurchases($a['image_path'] ?? null);

                $row = self::buildPurchaseRow($a, $shopId, $managerId, $userId);
                $row['supplier_id'] = $this->resolveSupplierId($a, $userId);   // Spec 002 FR-105
                $row['created_at'] = now();

                // Keep the purchase insert + its line items atomic on the MAIN (mysql)
                // connection: a failure in copyLineItems() must not leave an orphan
                // purchase row that blocks this invoice as a "duplicate" purchase_no.
                // The cross-connection sqlite link write is DELIBERATELY kept OUT of
                // this transaction (see below) — an sqlite autocommit inside a mysql tx
                // survives a mysql rollback and strands the invoice as "posted" against
                // a purchase that never committed (phantom-posted divergence).
                $purchaseId = DB::transaction(function () use ($row, $inv) {
                    $purchaseId = DB::table('purchase')->insertGetId($row);

                    // Copy extracted line items -> purchase_items (Spec 002 FR-102).
                    $this->copyLineItems($inv, $purchaseId);

                    return $purchaseId;
                });

                // Record the link on the isolated (invoices/sqlite) side ONLY AFTER the
                // mysql transaction has committed. Failure modes are now both safe:
                //   - mysql rolls back  -> exception above, link never written, invoice
                //     stays eligible and re-postable (no divergence).
                //   - mysql commits, link write fails -> purchase exists but invoice is
                //     unlinked; a later re-push is caught by the purchase_no UNIQUE guard
                //     (classified as a duplicate), so it can NEVER double-create.
                // Log + record a reconcile item so the operator can fix the link by hand.
                try {
                    $inv->forceFill(['purchase_id' => $purchaseId, 'mapped_at' => now()])->save();
                } catch (\Throwable $linkEx) {
                    Log::error('invoice push: purchase committed but sqlite link write failed', [
                        'invoice_id' => (int) $inv->id,
                        'purchase_id' => $purchaseId,
                        'invoice_number' => $no,
                        'batch_id' => $inv->batch_id ?? ($a['batch_id'] ?? null),
                        'error' => $linkEx->getMessage(),
                    ]);
                    $summary['link_errors'][] = [
                        'invoice_id' => (int) $inv->id,
                        'invoice_number' => $no,
                        'purchase_id' => $purchaseId,
                        'message' => 'رُحّلت الفاتورة إلى المشترى #'.$purchaseId.' لكن تعذّر ربطها (يلزم ربط يدوي): '.$linkEx->getMessage(),
                    ];
                }

                // Spec 001 FR-006 — audit the approval (invoice -> purchase).
                // Kept outside the transaction: a logging failure must not roll back the
                // purchase that was already created.
                \App\Services\AuditLogger::log('invoice', (int) $inv->id, \App\Services\AuditLogger::APPROVE, [
                    'batch_id' => $inv->batch_id,
                    'note' => 'مُرحّلة إلى المشتريات #'.$purchaseId,
                ]);

                // Spec 015 — auto-create a cashbox سند قبض (money OUT) for the posted
                // purchase so الصندوق reflects purchases, not only rent. Best-effort:
                // a cashbox failure must NOT roll back the already-committed purchase.
                try {
                    $amt = (float) ($a['total_incl_vat'] ?? 0);
                    if ($amt > 0) {
                        app(\App\Services\CashboxService::class)->recordReceipt([
                            'source_type' => 'purchase',
                            'source_id' => $purchaseId,
                            'direction' => 'out',
                            'amount' => $amt,
                            'receipt_date' => now()->toDateString(),
                            'payer_name' => $a['supplier_name'] ?? null,
                            'received_by' => $userId,
                            'note' => 'ترحيل فاتورة مشتريات — رقم '.($no ?: '—').' (مشترى #'.$purchaseId.')',
                            'create_user' => $userId,
                        ]);
                    }
                } catch (\Throwable $cbx) {
                    Log::warning('invoice push: cashbox سند creation failed (purchase kept)', [
                        'purchase_id' => $purchaseId,
                        'error' => $cbx->getMessage(),
                    ]);
                }

                // Also add the invoice image to the purchase's attachments (المرفقات).
                $this->attachToPurchase($purchaseId, $a['image_path'] ?? null, $userId, $attachTableExists, $attachMap, $summary);

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
        });

        Log::info('invoice push summary', [
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'shop_id' => $shopId,
            'manager_id' => $managerId,
            'pushed' => $summary['pushed'],
            'pushed_ids' => $summary['pushed_ids'],
            'already_mapped' => $summary['already_mapped'],
            'ineligible' => $summary['ineligible'],
            'ineligible_details' => $summary['ineligible_details'],
            'duplicates' => count($summary['duplicates']),
            'fuzzy_duplicates' => count($summary['fuzzy_duplicates']),
            'errors' => count($summary['errors']),
        ]);

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
     * The name to store on the purchase row for a supplier the master already knows
     * by tax number — otherwise whatever was printed on the invoice.
     *
     * Suppliers with a bilingual header (نهلة الوادي prints Arabic and English on
     * separate lines) come back differently on every invoice: 34 spellings of the
     * one company on نور الصباح, 16 on صباح النور, all tax 300975259400003. The
     * supplier LINK was always correct, but purchase_respon kept the raw text and
     * «اسم المورد» searches LIKE against it, so the client only ever found a slice
     * of his own invoices. Tax number identifies a company; the master names it.
     *
     * Deliberately tax-only: a fuzzy name match must never rename someone's
     * supplier, and an unknown tax number keeps exactly what was printed.
     */
    public static function canonicalSupplierName($taxNumber, $crNumber = null, $rawName = null): ?string
    {
        $raw = trim((string) $rawName);
        $fallback = $raw === '' ? null : $raw;

        $tax = preg_replace('/\D+/', '', self::arabicDigitsToAscii((string) $taxNumber));
        $cr = preg_replace('/\D+/', '', self::arabicDigitsToAscii((string) $crNumber));

        // Two unique keys, tried in order of authority. The tax number is the primary
        // one; the commercial registration is the safety net for when OCR mangles it —
        // this data holds a "300" and a 14-digit tax number, and a supplier identified
        // by neither key would otherwise start a fresh spelling of an existing company.
        $keys = [];
        if (self::isPlausibleTax($tax)) {
            $keys[] = ['tax_number', $tax];
        }
        if (self::isPlausibleCr($cr)) {
            $keys[] = ['cr_number', $cr];
        }
        if (! $keys) {
            return $fallback;
        }

        // Fail-open: naming a supplier is a nicety, pushing the invoice is the job.
        // An unreachable suppliers table must never cost the client a purchase row.
        foreach ($keys as [$column, $value]) {
            try {
                $canonical = trim((string) \App\Models\Supplier::where($column, $value)->value('name'));
            } catch (\Throwable $e) {
                return $fallback;
            }
            if ($canonical !== '') {
                return $canonical;
            }
        }

        return $fallback;
    }

    /**
     * A Saudi VAT number is 15 digits, starts with 3 and ends with 3.
     *
     * Anything else is a misread, not an identity. This data holds a tax number of
     * "300" that would have merged «محطة سهل» with «WALEED TURNERY», and a misread
     * 15-digit one that pulled 22 unrelated vendors under a single name.
     */
    public static function isPlausibleTax($tax): bool
    {
        $t = preg_replace('/\D+/', '', self::arabicDigitsToAscii((string) $tax));

        return strlen($t) === 15 && str_starts_with($t, '3') && str_ends_with($t, '3');
    }

    /**
     * A Saudi commercial registration is 10 digits. Anything shorter or longer came
     * off a bad scan and is not evidence that two invoices are the same company.
     */
    public static function isPlausibleCr($cr): bool
    {
        return strlen(preg_replace('/\D+/', '', self::arabicDigitsToAscii((string) $cr))) === 10;
    }

    /**
     * Record a supplier's commercial registration the first time we see a good one.
     *
     * canonicalSupplierName() falls back to the CR when the tax number is misread,
     * but that only helps if the master carries CRs — most rows predate the field.
     * Every matched invoice that prints a well-formed CR teaches it one, so the
     * safety net fills itself in as invoices arrive. Only ever fills a BLANK field:
     * overwriting a known CR with a fresh reading would let one bad scan rewrite an
     * identity, which is the failure mode this whole change exists to avoid.
     */
    private static function learnCrNumber($supplier, $crNumber): void
    {
        if (! $supplier || filled($supplier->cr_number) || ! self::isPlausibleCr($crNumber)) {
            return;
        }
        $cr = preg_replace('/\D+/', '', self::arabicDigitsToAscii((string) $crNumber));

        try {
            // Guarded on blank so concurrent pushes cannot fight over it.
            \App\Models\Supplier::where('id', $supplier->id)
                ->where(fn ($q) => $q->whereNull('cr_number')->orWhere('cr_number', ''))
                ->update(['cr_number' => $cr]);
        } catch (\Throwable $e) {
            // Fail-open, exactly like naming: never cost the client a purchase row.
            Log::warning('could not record supplier CR', ['supplier' => $supplier->id, 'reason' => $e->getMessage()]);
        }
    }

    /** ٣٠٠ -> 300, so a tax number typed in Arabic-Indic digits still matches. */
    private static function arabicDigitsToAscii(string $v): string
    {
        return strtr($v, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
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
            self::learnCrNumber($result['match'], $a['commercial_registration'] ?? null);

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
