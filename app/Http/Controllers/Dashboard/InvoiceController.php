<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApimtitTrait;
use App\Jobs\ProcessInvoiceBatch;
use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Models\InvoiceItem;
use App\Models\Shop;
use App\Services\AiSubscriptionGate;
use App\Services\AuditLogger;
use App\Services\InvoiceBatchSummarizer;
use App\Services\InvoicePurchaseMapper;
use App\Services\ZatcaQrGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Perm;

/**
 * AI invoice-extraction dashboard: upload a PDF (1 or many invoices) → background
 * extraction → live results table + grand total + inline correction.
 * All data lives in the isolated `invoices` DB connection (see the models).
 */
class InvoiceController extends Controller
{
    use ApimtitTrait; // provides get_manager() (admin -> all, others -> assigned)

    // NOTE: the parent dashboard route group already applies `auth`. Permission
    // wiring (ishaveaccess) can be added once a per_controller row is seeded;
    // admins (emp_job==1) bypass it anyway.

    public function index(Request $request)
    {
        $page_title = 'استخراج الفواتير';

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $batches = InvoiceBatch::query()
            // SECURITY: non-admins only ever see their own batches. Must survive any refactor.
            ->when(Auth::user()->emp_job != 1, fn ($q) => $q->where('user_id', Auth::id()))
            ->when($filters['q'] !== '', fn ($q) => $q->where('original_filename', 'like', '%'.$filters['q'].'%'))
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.invoices.index', compact('page_title', 'batches', 'filters'));
    }

    public function create()
    {
        $page_title = 'رفع فاتورة PDF';

        // Spec 007 — remaining-quota banner on the upload screen.
        $subscription = app(AiSubscriptionGate::class)->check();

        return view('dashboard.invoices.upload', compact('page_title', 'subscription'));
    }

    public function store(Request $request)
    {
        // Spec 007 — block the upload up front (before any file I/O or job
        // dispatch) when the AI subscription is inactive/expired/quota-
        // exhausted, with a clear Arabic message instead of a mid-pipeline
        // failure or 500.
        try {
            app(AiSubscriptionGate::class)->assertAllowed();
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message_out' => $e->getMessage()], 422);
        }

        // Spec 002 FR-101 — accept PDF OR a scanned image (JPG/PNG/JPEG/WEBP).
        $validated = $request->validate([
            'pdf' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:51200', // 50 MB
        ]);

        $file = $request->file('pdf');
        if (! $file->isValid()) {
            return response()->json(['status' => false, 'message_out' => 'الملف غير صالح، حاول مرة أخرى'], 422);
        }

        $dir = public_path('uploads/invoices/pdf');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $name = Str::random(8).'_'.time().'.'.$ext;
        $file->move($dir, $name);

        $batch = InvoiceBatch::create([
            'user_id' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'pdf_path' => 'uploads/invoices/pdf/'.$name,
            'status' => 'pending',
            'model_used' => config('services.gemini.default_model'),
        ]);

        ProcessInvoiceBatch::dispatch($batch->id, null, config('services.gemini.default_mode', 'split'));

        return response()->json([
            'status' => true,
            'batch_id' => $batch->id,
            'redirect' => route('dashboard.invoices.show', $batch->id),
        ]);
    }

    public function show($id)
    {
        $batch = $this->findOwned($id);
        $page_title = 'نتائج الفاتورة #'.$batch->id;

        // For the "push to purchases" panel: shop XOR manager must be chosen.
        $shops = Shop::get();
        $managers = $this->get_manager();
        $canPush = (bool) Perm::get_function_access(55);

        // Feature A — batch AI summary. Only computed once the batch is finished
        // extracting; a mid-run batch's numbers would just churn on every poll.
        $aiSummary = $batch->status === 'done'
            ? app(InvoiceBatchSummarizer::class)->summarize($batch->id)
            : null;

        return view('dashboard.invoices.show', compact('page_title', 'batch', 'shops', 'managers', 'canPush', 'aiSummary'));
    }

    /**
     * Push every eligible invoice of this batch into the main `purchase` table,
     * assigning the chosen shop XOR manager. Reuses the purchase-create permission.
     */
    public function pushToPurchase(Request $request, $id)
    {
        $batch = $this->findOwned($id);

        if (! Perm::get_function_access(55)) {
            return response()->json(['status' => false, 'message_out' => 'ليس لديك صلاحية لإضافة المشتريات'], 403);
        }

        $shopId = $request->filled('shop_id') ? (int) $request->shop_id : null;
        $managerId = $request->filled('manager_id') ? (int) $request->manager_id : null;

        if (! $shopId && ! $managerId) {
            return response()->json(['status' => false, 'message_out' => 'الرجاء اختيار قائد مجموعة أو محل'], 422);
        }
        if ($shopId && $managerId) {
            return response()->json(['status' => false, 'message_out' => 'اختر قائد مجموعة أو محل وليس كليهما'], 422);
        }

        $summary = app(InvoicePurchaseMapper::class)->push($batch, $shopId, $managerId, Auth::id());

        $msg = 'تم ترحيل '.$summary['pushed'].' فاتورة إلى المشتريات';
        if ($summary['attached']) {
            $msg .= ' (أُرفقت '.$summary['attached'].' صورة)';
        }
        if (count($summary['duplicates'])) {
            $msg .= ' — تخطّي '.count($summary['duplicates']).' مكررة';
        }
        if (count($summary['fuzzy_duplicates'] ?? [])) {
            $msg .= ' — '.count($summary['fuzzy_duplicates']).' مشتبه بتكرارها (تحتاج مراجعة)';
        }
        if ($summary['already_mapped']) {
            $msg .= ' — '.$summary['already_mapped'].' مُرحّلة مسبقاً';
        }
        if ($summary['ineligible']) {
            $msg .= ' — '.$summary['ineligible'].' غير مؤهلة (تحتاج مراجعة أو ناقصة)';
        }
        if (count($summary['errors'])) {
            $msg .= ' — '.count($summary['errors']).' أخطاء';
        }

        return response()->json(['status' => true, 'message_out' => $msg, 'summary' => $summary]);
    }

    /**
     * Delete a whole batch and every invoice in it. Any invoice already posted to
     * the main `purchase` table is reversed first (purchase + its items + attachments
     * removed), then the isolated-side batch/invoices are dropped.
     */
    public function destroy($id)
    {
        $batch = $this->findOwned($id);

        $reversed = 0;
        foreach ($batch->invoices()->get() as $inv) {
            if (filled($inv->purchase_id)) {
                $this->reversePurchase((int) $inv->purchase_id);
                $inv->forceFill(['purchase_id' => null, 'mapped_at' => null])->save();
                $reversed++;
            }
            InvoiceItem::on($inv->getConnectionName())->where('invoice_id', $inv->id)->delete();
        }

        // FK batch_id cascades to invoices, but delete explicitly for clarity/safety.
        $batch->invoices()->delete();
        $batch->delete();

        AuditLogger::log('invoice', (int) $id, AuditLogger::DELETE, [
            'note' => 'حُذفت الدفعة'.($reversed ? " (عُكس ترحيل {$reversed} فاتورة من المشتريات)" : ''),
        ]);

        return response()->json([
            'status' => true,
            'message_out' => 'تم حذف الدفعة'.($reversed ? " وعكس ترحيل {$reversed} فاتورة" : ''),
            'redirect' => route('dashboard.invoices.index'),
        ]);
    }

    /**
     * Delete a single invoice from a batch. If it was posted to `purchase`, its
     * purchase row (+ items + attachments) is reversed first.
     */
    public function destroyInvoice($batchId, $invoiceId)
    {
        $batch = $this->findOwned($batchId);

        $inv = $batch->invoices()->whereKey($invoiceId)->firstOrFail();

        $reversed = false;
        if (filled($inv->purchase_id)) {
            $this->reversePurchase((int) $inv->purchase_id);
            $reversed = true;
        }

        InvoiceItem::on($inv->getConnectionName())->where('invoice_id', $inv->id)->delete();
        $inv->delete();
        $batch->recomputeGrandTotal();

        AuditLogger::log('invoice', (int) $inv->id, AuditLogger::DELETE, [
            'batch_id' => (int) $batch->id,
            'note' => 'حُذفت الفاتورة'.($reversed ? ' (عُكس ترحيلها من المشتريات)' : ''),
        ]);

        return response()->json([
            'status' => true,
            'message_out' => 'تم حذف الفاتورة'.($reversed ? ' وعكس ترحيلها' : ''),
        ]);
    }

    /**
     * Reverse a posted purchase on the main connection: remove its attachments and
     * line items, then the purchase row itself, all in one transaction. Best-effort
     * on the child tables (they may not exist / differ in prod) — the purchase row
     * delete is the load-bearing part.
     */
    private function reversePurchase(int $purchaseId): void
    {
        DB::transaction(function () use ($purchaseId) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('purchase_attach')) {
                    DB::table('purchase_attach')->where('purchase_id', $purchaseId)->delete();
                }
            } catch (\Throwable $e) {
                // best-effort
            }
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('purchase_items')) {
                    DB::table('purchase_items')->where('purchase_id', $purchaseId)->delete();
                }
            } catch (\Throwable $e) {
                // best-effort
            }
            DB::table('purchase')->where('purchase_id', $purchaseId)->delete();
        });
    }

    /**
     * Spec 002 FR-107 — the review/approval screen for a batch. Shows every
     * invoice's page image, extracted fields with per-field confidence, line
     * items, and validation notes so a human can Approve/Reject/Reprocess/
     * Save-draft each one. No record is finalized without an explicit Approve.
     */
    public function review($id)
    {
        $batch = $this->findOwned($id);
        $page_title = 'مراجعة الفواتير — دفعة #'.$batch->id;

        $invoices = $batch->invoices()->with('items')->orderBy('page_number')->get();
        $invoices->each(function (Invoice $i) use ($batch) {
            $i->image_url = $this->imageUrl($batch->id, $i->image_path);
        });

        return view('dashboard.invoices.review', compact('page_title', 'batch', 'invoices'));
    }

    /** Approve one invoice: clears needs_review so it counts as finalized. */
    public function approve($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorizeBatch($invoice->batch);

        if ($invoice->status === 'failed') {
            $invoice->status = 'done';
        }
        $invoice->needs_review = false;
        $invoice->save();

        AuditLogger::log('invoice', (int) $invoice->id, AuditLogger::APPROVE, ['batch_id' => $invoice->batch_id]);

        $grand = $invoice->batch->recomputeGrandTotal();

        return response()->json(['status' => true, 'message_out' => 'تم اعتماد الفاتورة', 'grand_total' => $grand]);
    }

    /** Reject one invoice: marks it rejected so it is excluded from purchases. */
    public function reject($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorizeBatch($invoice->batch);

        $invoice->status = 'rejected';
        $invoice->needs_review = false;
        $invoice->save();

        AuditLogger::log('invoice', (int) $invoice->id, AuditLogger::REJECT, ['batch_id' => $invoice->batch_id]);

        $grand = $invoice->batch->recomputeGrandTotal();

        return response()->json(['status' => true, 'message_out' => 'تم رفض الفاتورة', 'grand_total' => $grand]);
    }

    /**
     * Re-run extraction for the invoice's batch. The pipeline upserts on
     * (batch_id, page_number) so this refreshes every page's row in place.
     */
    public function reprocess($id)
    {
        $invoice = Invoice::findOrFail($id);
        $batch = $invoice->batch;
        $this->authorizeBatch($batch);

        if ($batch->status === 'processing') {
            return response()->json([
                'status' => false,
                'message_out' => 'الدفعة قيد المعالجة بالفعل، يرجى الانتظار',
            ], 409);
        }

        $invoice->forceFill(['status' => 'pending', 'error_message' => null])->save();
        $batch->forceFill(['status' => 'processing', 'error_message' => null])->save();

        ProcessInvoiceBatch::dispatch($batch->id, $batch->model_used, config('services.gemini.default_mode', 'split'));

        AuditLogger::log('invoice', (int) $invoice->id, AuditLogger::REPROCESS, ['batch_id' => $batch->id]);

        return response()->json(['status' => true, 'message_out' => 'تمت جدولة إعادة معالجة الدفعة']);
    }

    /**
     * Feature B — smart re-scan (model escalation). Re-runs the WHOLE batch
     * through a stronger, slower Gemini model (config('services.gemini.rescan_model'))
     * instead of the default one — an opt-in "read it again, more carefully" for a
     * batch with unclear scans. The pipeline upserts on (batch_id, page_number) so
     * this refreshes every page's row in place, same as reprocess().
     */
    public function rescan($id)
    {
        $batch = $this->findOwned($id);

        $hardModel = config('services.gemini.rescan_model');

        $batch->forceFill(['status' => 'pending', 'error_message' => null])->save();

        ProcessInvoiceBatch::dispatch($batch->id, $hardModel, config('services.gemini.default_mode', 'split'));

        AuditLogger::log('invoice', null, AuditLogger::REPROCESS, [
            'batch_id' => $batch->id,
            'note' => 'smart re-scan with stronger model: '.$hardModel,
        ]);

        return response()->json(['status' => true, 'message_out' => 'تمت جدولة إعادة الفحص بدقة أعلى']);
    }

    /** Save the current field values without finalizing the invoice. */
    public function draft($id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorizeBatch($invoice->batch);

        $invoice->status = 'draft';
        $invoice->save();

        AuditLogger::log('invoice', (int) $invoice->id, AuditLogger::EDIT, [
            'batch_id' => $invoice->batch_id,
            'note' => 'saved as draft',
        ]);

        return response()->json(['status' => true, 'message_out' => 'تم حفظ المسودة']);
    }

    /**
     * Spec 002 FR-108 — failed invoices needing a Reprocess or manual fix.
     * Optionally scoped to a single batch.
     */
    public function error($batchId = null)
    {
        $page_title = 'الفواتير التي تعذّر استخراجها';

        $invoices = Invoice::query()
            ->where('status', 'failed')
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->when(Auth::user()->emp_job != 1, fn ($q) => $q->whereHas(
                'batch', fn ($b) => $b->where('user_id', Auth::id())
            ))
            ->with('batch')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $invoices->each(function (Invoice $i) {
            $i->image_url = $this->imageUrl($i->batch_id, $i->image_path);
        });

        $batches = InvoiceBatch::query()
            ->where('status', 'failed')
            ->when($batchId, fn ($q) => $q->where('id', $batchId))
            ->when(Auth::user()->emp_job != 1, fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('dashboard.invoices.error', compact('page_title', 'invoices', 'batches', 'batchId'));
    }

    /**
     * Manual-entry form on the error screen: fills a failed invoice's core
     * fields by hand. Still requires an explicit Approve to finalize (Spec
     * 002 FR-108) — this only clears the failure and marks it extracted.
     */
    public function manualEntry(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $this->authorizeBatch($invoice->batch);

        $fields = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'supplier_tax_number' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'amount_before_vat' => 'nullable|numeric',
            'vat_amount' => 'nullable|numeric',
            'total_incl_vat' => 'nullable|numeric',
        ]);

        $invoice->fill($fields);
        $invoice->status = 'done';
        $invoice->needs_review = true; // manual entry alone never finalizes the record
        $invoice->error_message = null;
        $invoice->save();

        AuditLogger::log('invoice', (int) $invoice->id, AuditLogger::EDIT, [
            'batch_id' => $invoice->batch_id,
            'note' => 'manual entry',
        ]);

        $grand = $invoice->batch->recomputeGrandTotal();

        return response()->json(['status' => true, 'message_out' => 'تم حفظ البيانات يدويًا، بانتظار الاعتماد', 'grand_total' => $grand]);
    }

    /**
     * Spec 002 FR-109 — aggregate dashboard over the isolated `invoices`
     * connection: counts, totals, duplicates, rejections, AI success rate,
     * average processing time, top suppliers, top items.
     */
    public function report()
    {
        $page_title = 'تقرير الفواتير';

        $scope = function ($q) {
            $q->when(Auth::user()->emp_job != 1, fn ($q2) => $q2->whereHas(
                'batch', fn ($b) => $b->where('user_id', Auth::id())
            ));
        };

        $base = fn () => Invoice::query()->tap($scope);

        $today = $base()->whereDate('created_at', now()->toDateString())->count();
        $thisMonth = $base()->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $totalPurchases = (float) $base()->sum('total_incl_vat');
        $totalVat = (float) $base()->sum('vat_amount');
        $duplicates = $base()->where(function ($q) {
            $q->where('needs_review', true)->orWhere('validation_notes', 'like', '%مكرر%');
        })->count();
        $rejected = $base()->where('status', 'rejected')->count();
        $needsReview = $base()->where('needs_review', true)->count();
        $total = $base()->count();
        $done = $base()->where('status', 'done')->count();
        $successRate = $total > 0 ? round(($done / $total) * 100, 1) : 0.0;
        $avgProcessingMs = (float) ($base()->whereNotNull('processing_ms')->avg('processing_ms') ?? 0);

        $topSuppliers = $base()
            ->whereNotNull('supplier_name')
            ->select('supplier_name', DB::raw('count(*) as cnt'), DB::raw('sum(total_incl_vat) as total'))
            ->groupBy('supplier_name')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $topItemsQuery = InvoiceItem::query()
            ->whereNotNull('name')
            ->when(Auth::user()->emp_job != 1, fn ($q) => $q->whereHas(
                'invoice.batch', fn ($b) => $b->where('user_id', Auth::id())
            ))
            ->select('name', DB::raw('count(*) as cnt'), DB::raw('sum(line_total) as total'))
            ->groupBy('name')
            ->orderByDesc('cnt')
            ->limit(10);
        $topItems = $topItemsQuery->get();

        $stats = compact(
            'today', 'thisMonth', 'totalPurchases', 'totalVat', 'duplicates',
            'rejected', 'needsReview', 'successRate', 'avgProcessingMs', 'topSuppliers', 'topItems'
        );

        return view('dashboard.invoices.report', compact('page_title', 'stats'));
    }

    /** Polled by the results page to render live progress + rows. */
    public function status($id)
    {
        $batch = $this->findOwned($id);
        $total = max(1, (int) $batch->total_pages);

        $batchInvoices = $batch->invoices()->orderBy('page_number')->get();

        // Proactive duplicate flag: which of this batch's invoice numbers already exist
        // in the main `purchase` table (i.e. would be blocked on push). One cheap query.
        $existingNos = [];
        try {
            $numbers = $batchInvoices->pluck('invoice_number')->filter()->map(fn ($n) => trim((string) $n))->unique()->values()->all();
            if (! empty($numbers)) {
                $existingNos = array_flip(array_map('strval', DB::table('purchase')->whereIn('purchase_no', $numbers)->pluck('purchase_no')->all()));
            }
        } catch (\Throwable $e) {
            $existingNos = [];
        }

        $invoices = $batchInvoices->map(function (Invoice $i) use ($batch, $existingNos) {
            // ZATCA Phase-1 QR — only for invoices that have a total (i.e.
            // extraction actually produced numbers worth encoding).
            $zatcaQr = null;
            $zatcaQrImage = null;
            if ($i->total_incl_vat !== null) {
                $qr = $this->zatcaQr()->qrBase64($i);
                // qrBase64 returns '' when ZATCA seller identity isn't configured
                // (config/zatca.php seller_name + vat_number) — skip the QR then.
                if ($qr !== '') {
                    $zatcaQr = $qr;
                    $zatcaQrImage = $this->zatcaQrImageDataUri($qr);
                }
            }

            return [
                'id' => $i->id,
                'page_number' => $i->page_number,
                'supplier_name' => $i->supplier_name,
                'supplier_tax_number' => $i->supplier_tax_number,
                'invoice_number' => $i->invoice_number,
                'invoice_date' => $i->invoice_date?->format('Y-m-d'),
                'amount_before_vat' => $i->amount_before_vat,
                'vat_amount' => $i->vat_amount,
                'total_incl_vat' => $i->total_incl_vat,
                'needs_review' => (bool) $i->needs_review,
                'validation_notes' => $i->validation_notes,
                'status' => $i->status,
                'image_path' => $i->image_path,
                'image_url' => $this->imageUrl($batch->id, $i->image_path),
                'image_quality' => $i->image_quality,
                'purchase_id' => $i->purchase_id,
                // True when this invoice number already exists in purchases from another
                // batch (would be skipped as a duplicate on push). Not flagged for the
                // invoice that is itself already mapped (that shows the "posted" badge).
                'duplicate_in_purchase' => ! $i->purchase_id && isset($existingNos[trim((string) $i->invoice_number)]),
                'zatca_qr' => $zatcaQr, // base64 TLV payload (Phase-1)
                'zatca_qr_image' => $zatcaQrImage, // data:image/png;base64,... rendered via TCPDF 2D barcode
            ];
        });

        return response()->json([
            'status' => $batch->status,
            'total_pages' => $batch->total_pages,
            'processed_pages' => $batch->processed_pages,
            'percent' => min(100, (int) round(($batch->processed_pages / $total) * 100)),
            'grand_total' => (float) $batch->grand_total,
            'input_tokens' => (int) $batch->input_tokens,
            'output_tokens' => (int) $batch->output_tokens,
            'est_cost_usd' => (float) $batch->est_cost_usd,
            'est_cost_sar' => round((float) $batch->est_cost_usd * (float) config('services.gemini.usd_to_sar', 3.75), 4),
            'model_used' => $batch->model_used,
            'error_message' => $batch->error_message,
            'invoices' => $invoices,
        ]);
    }

    /** Inline-edit one field of one invoice, then recompute the grand total. */
    public function correct(Request $request, $id)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $editable = [
            'supplier_name', 'supplier_tax_number', 'invoice_number', 'invoice_date',
            'amount_before_vat', 'vat_amount', 'total_incl_vat',
        ];
        if (! in_array($field, $editable, true)) {
            return response()->json(['status' => false, 'message_out' => 'حقل غير قابل للتعديل'], 422);
        }

        $invoice = Invoice::findOrFail($id);
        $this->authorizeBatch($invoice->batch);

        if (in_array($field, ['amount_before_vat', 'vat_amount', 'total_incl_vat'], true)) {
            $value = is_numeric($value) ? (float) $value : null;
        }
        $old = $invoice->{$field};
        $invoice->{$field} = $value === '' ? null : $value;
        $invoice->needs_review = false;
        $invoice->save();

        // Spec 001 FR-006 — audit the manual correction (actor + old/new + timestamp).
        \App\Services\AuditLogger::log('invoice', (int) $invoice->id, \App\Services\AuditLogger::EDIT, [
            'batch_id' => $invoice->batch_id,
            'field' => $field,
            'old' => $old,
            'new' => $invoice->{$field},
        ]);

        $grand = $invoice->batch->recomputeGrandTotal();

        return response()->json(['status' => true, 'grand_total' => $grand]);
    }

    /** Build a Laravel-served URL for a page image (web servers here don't serve the symlinked uploads dir). */
    private function imageUrl(int $batchId, ?string $imagePath): ?string
    {
        if (! $imagePath || ! preg_match('/\.(png|jpe?g|webp|gif)$/i', $imagePath)) {
            return null; // PDFs / missing
        }

        return route('dashboard.invoices.file', ['id' => $batchId, 'name' => basename($imagePath)]);
    }

    /** Stream a per-page invoice image through the app (auth + ownership enforced). */
    public function file($id, $name)
    {
        $batch = $this->findOwned($id);
        $name = basename($name); // no path traversal
        foreach ([
            public_path('uploads/invoices/pages/batch_'.$batch->id.'/'.$name),
            storage_path('app/public/invoices/pages/batch_'.$batch->id.'/'.$name),
        ] as $path) {
            if (is_file($path)) {
                return response()->file($path);
            }
        }
        abort(404);
    }

    private function zatcaQr(): ZatcaQrGenerator
    {
        return app(ZatcaQrGenerator::class);
    }

    /**
     * Render a ZATCA Phase-1 TLV base64 payload as a QR PNG (data URI) using
     * TCPDF's built-in 2D barcode generator — used for both the on-screen
     * invoice view and the printed/PDF output (same Blade template).
     * Returns null (never throws) if the barcode/image backend is unavailable,
     * so a rendering failure never breaks the results page.
     */
    private function zatcaQrImageDataUri(string $base64Tlv): ?string
    {
        try {
            $barcode = new \TCPDF2DBarcode($base64Tlv, 'QRCODE,M');
            $png = $barcode->getBarcodePngData(4, 4);
            if ($png === false || $png === null) {
                return null;
            }

            return 'data:image/png;base64,'.base64_encode($png);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function findOwned($id): InvoiceBatch
    {
        $batch = InvoiceBatch::findOrFail($id);
        $this->authorizeBatch($batch);

        return $batch;
    }

    private function authorizeBatch(InvoiceBatch $batch): void
    {
        if (Auth::user()->emp_job != 1 && $batch->user_id != Auth::id()) {
            abort(403);
        }
    }
}
