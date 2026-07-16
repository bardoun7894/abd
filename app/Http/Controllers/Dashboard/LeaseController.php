<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLeaseBatch;
use App\Models\LeaseBatch;
use App\Models\LeaseContract;
use App\Models\LeaseExtraction;
use App\Models\LeasePayment;
use App\Services\LeaseScheduleGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * AI lease-extraction dashboard (Spec 003): upload a PDF → background extraction →
 * live results table + inline correction → approve to create a LeaseContract +
 * generated payment schedule. Staging data lives in the isolated `invoices`
 * connection; approved contracts/payments live in the main DB.
 *
 * Mirrors InvoiceController's structure/conventions.
 */
class LeaseController extends Controller
{
    // NOTE: the parent dashboard route group already applies `auth`. Permission
    // wiring (ishaveaccess) can be added once a per_controller row is seeded;
    // admins (emp_job==1) bypass it anyway.

    public function index()
    {
        $page_title = 'عقود الإيجار — استخراج بالذكاء الاصطناعي';
        $batches = LeaseBatch::query()
            ->when(Auth::user()->emp_job != 1, fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('dashboard.leases.index', compact('page_title', 'batches'));
    }

    public function create()
    {
        $page_title = 'رفع عقد إيجار PDF';

        return view('dashboard.leases.upload', compact('page_title'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:51200', // 50 MB
        ]);

        $file = $request->file('pdf');
        if (! $file->isValid()) {
            return response()->json(['status' => false, 'message_out' => 'الملف غير صالح، حاول مرة أخرى'], 422);
        }

        $dir = public_path('uploads/leases/pdf');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = Str::random(8).'_'.time().'.pdf';
        $file->move($dir, $name);

        $batch = LeaseBatch::create([
            'user_id' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'pdf_path' => 'uploads/leases/pdf/'.$name,
            'status' => 'pending',
            'model_used' => config('services.gemini.default_model'),
        ]);

        ProcessLeaseBatch::dispatch($batch->id);

        return response()->json([
            'status' => true,
            'batch_id' => $batch->id,
            'redirect' => route('dashboard.leases.show', $batch->id),
        ]);
    }

    public function show($id)
    {
        $batch = $this->findOwned($id);
        $page_title = 'نتائج عقد الإيجار #'.$batch->id;

        return view('dashboard.leases.show', compact('page_title', 'batch'));
    }

    /** Polled by the results page to render live progress + rows. */
    public function status($id)
    {
        $batch = $this->findOwned($id);
        $total = max(1, (int) $batch->total_pages);

        $extractions = $batch->extractions()->orderBy('page_number')->get()->map(fn (LeaseExtraction $e) => [
            'id' => $e->id,
            'page_number' => $e->page_number,
            'contract_no' => $e->contract_no,
            'tenant_name' => $e->tenant_name,
            'landlord_name' => $e->landlord_name,
            'unit' => $e->unit,
            'start_date' => $e->start_date?->format('Y-m-d'),
            'end_date' => $e->end_date?->format('Y-m-d'),
            'rent_value' => $e->rent_value,
            'num_payments' => $e->num_payments,
            'payment_value' => $e->payment_value,
            'payment_frequency' => $e->payment_frequency,
            'needs_review' => (bool) $e->needs_review,
            'validation_notes' => $e->validation_notes,
            'status' => $e->status,
            'image_path' => $e->image_path,
            'image_url' => $this->imageUrl($batch->id, $e->image_path),
            'contract_id' => $e->contract_id,
        ]);

        return response()->json([
            'status' => $batch->status,
            'total_pages' => $batch->total_pages,
            'processed_pages' => $batch->processed_pages,
            'percent' => min(100, (int) round(($batch->processed_pages / $total) * 100)),
            'input_tokens' => (int) $batch->input_tokens,
            'output_tokens' => (int) $batch->output_tokens,
            'est_cost_usd' => (float) $batch->est_cost_usd,
            'est_cost_sar' => round((float) $batch->est_cost_usd * (float) config('services.gemini.usd_to_sar', 3.75), 4),
            'model_used' => $batch->model_used,
            'error_message' => $batch->error_message,
            'extractions' => $extractions,
        ]);
    }

    /** Inline-edit one field of one lease extraction, then clear its review flag. */
    public function correct(Request $request, $id)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $editable = [
            'contract_no', 'tenant_name', 'tenant_id_no', 'landlord_name', 'landlord_id_no',
            'property_no', 'unit', 'property_type', 'address',
            'start_date', 'end_date', 'duration',
            'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
            'deposit', 'payment_method',
            'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
        ];
        if (! in_array($field, $editable, true)) {
            return response()->json(['status' => false, 'message_out' => 'حقل غير قابل للتعديل'], 422);
        }

        $extraction = LeaseExtraction::findOrFail($id);
        $this->authorizeBatch($extraction->batch);

        if (in_array($field, ['rent_value', 'payment_value', 'deposit'], true)) {
            $value = is_numeric($value) ? (float) $value : null;
        } elseif ($field === 'num_payments') {
            $value = is_numeric($value) ? (int) $value : null;
        }
        $old = $extraction->{$field};
        $extraction->{$field} = $value === '' ? null : $value;
        $extraction->needs_review = false;
        $extraction->save();

        // Spec 001 FR-006 — audit the manual correction (actor + old/new + timestamp).
        \App\Services\AuditLogger::log('lease', (int) $extraction->id, \App\Services\AuditLogger::EDIT, [
            'batch_id' => $extraction->batch_id,
            'field' => $field,
            'old' => $old,
            'new' => $extraction->{$field},
        ]);

        return response()->json(['status' => true]);
    }

    /**
     * Approve an extraction: create the draft LeaseContract in the main DB, generate
     * its payment schedule, and link the extraction back to it (Spec 003 FR-202/203).
     */
    public function approve(Request $request, $id)
    {
        $extraction = LeaseExtraction::findOrFail($id);
        $this->authorizeBatch($extraction->batch);

        if ($extraction->contract_id) {
            return response()->json(['status' => false, 'message_out' => 'تمت الموافقة على هذا العقد مسبقاً'], 422);
        }

        $data = $extraction->only([
            'contract_no', 'tenant_name', 'tenant_id_no', 'landlord_name', 'landlord_id_no',
            'property_no', 'unit', 'property_type', 'address',
            'start_date', 'end_date', 'duration',
            'rent_value', 'num_payments', 'payment_value', 'payment_frequency',
            'deposit', 'payment_method',
            'renewal_terms', 'cancellation_terms', 'increase_terms', 'extra_terms',
        ]);
        $data['start_date'] = $extraction->start_date?->format('Y-m-d');
        $data['end_date'] = $extraction->end_date?->format('Y-m-d');

        if (empty($data['start_date']) || empty($data['rent_value'])) {
            return response()->json(['status' => false, 'message_out' => 'لا يمكن الموافقة: تاريخ البداية أو قيمة الإيجار مفقودة'], 422);
        }

        $contract = DB::transaction(function () use ($extraction, $data) {
            $contract = LeaseContract::create($data + [
                'attach_url' => $extraction->image_path,
                'extracted_text' => $extraction->extracted_text,
                'status' => 'active',
                'extraction_id' => $extraction->id,
                'create_user' => Auth::id(),
            ]);

            $rows = (new LeaseScheduleGenerator())->generate($data);
            foreach ($rows as $row) {
                LeasePayment::create($row + ['contract_id' => $contract->id]);
            }

            $extraction->forceFill(['contract_id' => $contract->id, 'mapped_at' => now()])->save();

            return $contract;
        });

        // Spec 001 FR-006 — audit the approval (extraction -> contract).
        \App\Services\AuditLogger::log('lease', (int) $extraction->id, \App\Services\AuditLogger::APPROVE, [
            'batch_id' => $extraction->batch_id,
            'note' => 'تمت الموافقة، أُنشئ العقد #'.$contract->id,
        ]);

        return response()->json(['status' => true, 'contract_id' => $contract->id, 'message_out' => 'تم إنشاء العقد وجدول الدفعات']);
    }

    /** Unprocessed-contracts screen (Spec 003 FR-206): failed / needs-review rows. */
    public function unprocessed()
    {
        $page_title = 'عقود غير معالَجة';

        $extractions = LeaseExtraction::query()
            ->whereIn('status', ['failed'])
            ->orWhere('needs_review', true)
            ->with('batch')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('dashboard.leases.unprocessed', compact('page_title', 'extractions'));
    }

    /**
     * Spec 003 FR-205 — rentals analytics dashboard: collection rate, revenue,
     * contract status mix, top/late tenants, and upcoming due. Aggregated from the
     * main-DB lease_contracts + lease_payments.
     */
    public function analytics()
    {
        $page_title = 'تحليلات الإيجارات';

        $contracts = LeaseContract::query();
        $total_contracts = (clone $contracts)->count();
        $active = (clone $contracts)->where('status', 'active')->count();
        $ended = (clone $contracts)->where(fn ($q) => $q->where('status', 'ended')->orWhereDate('end_date', '<', now()))->count();
        $renewable = (clone $contracts)->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))->count();
        $troubled = (clone $contracts)->where('status', 'troubled')->count();

        $due_total = (float) LeasePayment::sum('amount');
        $paid_total = (float) LeasePayment::whereIn('status', ['paid'])->sum(DB::raw('COALESCE(paid_amount, amount)'));
        $collection_rate = $due_total > 0 ? round($paid_total / $due_total * 100, 1) : 0.0;

        $monthly_revenue = (float) LeasePayment::where('status', 'paid')
            ->whereBetween('paid_date', [now()->startOfMonth(), now()->endOfMonth()])->sum(DB::raw('COALESCE(paid_amount, amount)'));
        $annual_revenue = (float) LeasePayment::where('status', 'paid')
            ->whereBetween('paid_date', [now()->startOfYear(), now()->endOfYear()])->sum(DB::raw('COALESCE(paid_amount, amount)'));

        $overdue = LeasePayment::where('status', '!=', 'paid')->whereDate('due_date', '<', now())->count();
        $upcoming = LeasePayment::where('status', '!=', 'paid')
            ->whereBetween('due_date', [now(), now()->addDays(30)])->count();

        // Top tenants by total contract value; most-late tenants by overdue count.
        $top_tenants = LeaseContract::select('tenant_name', DB::raw('SUM(rent_value) as total'))
            ->whereNotNull('tenant_name')->groupBy('tenant_name')->orderByDesc('total')->limit(10)->get();
        $late_tenants = LeasePayment::join('lease_contracts', 'lease_contracts.id', '=', 'lease_payments.contract_id')
            ->where('lease_payments.status', '!=', 'paid')->whereDate('lease_payments.due_date', '<', now())
            ->select('lease_contracts.tenant_name', DB::raw('COUNT(*) as late_count'))
            ->groupBy('lease_contracts.tenant_name')->orderByDesc('late_count')->limit(10)->get();

        $stats = compact(
            'total_contracts', 'active', 'ended', 'renewable', 'troubled',
            'due_total', 'paid_total', 'collection_rate', 'monthly_revenue', 'annual_revenue',
            'overdue', 'upcoming'
        );

        // Spec 006 T6-3 — "التوقعات المستقبلية للإيرادات" + "تحليل اتجاهات التحصيل
        // باستخدام الذكاء الاصطناعي". Numbers are computed in PHP (LeaseForecastService);
        // Gemini only phrases the trend narrative and gracefully falls back on failure.
        $forecastService = app(\App\Services\LeaseForecastService::class);
        $forecast = $forecastService->projectRevenue(6);
        $collection_history = $forecastService->collectionHistory(6);
        $trend = $forecastService->trendNarrative();

        return view('dashboard.leases.analytics', compact(
            'page_title', 'stats', 'top_tenants', 'late_tenants',
            'forecast', 'collection_history', 'trend'
        ));
    }

    /** Re-run AI extraction for one page (Spec 003 FR-206 "إعادة تشغيل الذكاء الاصطناعي"). */
    public function reprocess($id)
    {
        $extraction = LeaseExtraction::findOrFail($id);
        $this->authorizeBatch($extraction->batch);

        $extraction->forceFill(['status' => 'pending', 'error_message' => null])->save();

        \App\Services\AuditLogger::log('lease', (int) $extraction->id, \App\Services\AuditLogger::REPROCESS, [
            'batch_id' => $extraction->batch_id,
        ]);

        ProcessLeaseBatch::dispatch($extraction->batch_id);

        return response()->json(['status' => true, 'message_out' => 'أُعيدت جدولة القراءة']);
    }

    /** Build a Laravel-served URL for a page image (web servers here don't serve the symlinked uploads dir). */
    private function imageUrl(int $batchId, ?string $imagePath): ?string
    {
        if (! $imagePath || ! preg_match('/\.(png|jpe?g|webp|gif)$/i', $imagePath)) {
            return null; // PDFs / missing
        }

        return route('dashboard.leases.file', ['id' => $batchId, 'name' => basename($imagePath)]);
    }

    /** Stream a per-page lease image through the app (auth + ownership enforced). */
    public function file($id, $name)
    {
        $batch = $this->findOwned($id);
        $name = basename($name); // no path traversal
        $path = public_path('uploads/leases/pages/batch_'.$batch->id.'/'.$name);
        if (is_file($path)) {
            return response()->file($path);
        }
        abort(404);
    }

    private function findOwned($id): LeaseBatch
    {
        $batch = LeaseBatch::findOrFail($id);
        $this->authorizeBatch($batch);

        return $batch;
    }

    private function authorizeBatch(LeaseBatch $batch): void
    {
        if (Auth::user()->emp_job != 1 && $batch->user_id != Auth::id()) {
            abort(403);
        }
    }
}
