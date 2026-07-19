<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceBatch;
use App\Services\InvoicePipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * LOCAL-ONLY harness to interactively test invoice extraction in the browser.
 * Everything here touches ONLY the isolated `invoices` connection. The pipeline
 * runs synchronously so results render immediately — no queue, no polling.
 *
 * Gated on env('INVOICE_LOCAL_UI'): unset in prod -> every route 404s.
 * Also requires auth + admin (emp_job == 1) so the UI is never reachable by
 * unauthenticated or non-admin users even if the env flag is accidentally set.
 */
class InvoiceLocalTestController extends Controller
{
    private function guard(): void
    {
        abort_unless((bool) env('INVOICE_LOCAL_UI', false), 404);
        abort_unless(Auth::check(), 401);
        abort_unless((int) (Auth::user()->emp_job ?? 0) === 1, 403);

        // Keep the demo UI clean (no injected dev debug bar).
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }
    }

    public function index()
    {
        $this->guard();
        $batches = InvoiceBatch::withCount('invoices')->orderByDesc('id')->limit(50)->get();
        $totalInvoices = Invoice::count();
        $totalBatches = InvoiceBatch::count();

        return view('invoices_local.index', compact('batches', 'totalInvoices', 'totalBatches'));
    }

    public function store(Request $request)
    {
        $this->guard();
        $request->validate(['pdf' => 'required|file|mimes:pdf|max:51200']);

        $file = $request->file('pdf');
        $original = $file->getClientOriginalName();
        $dir = public_path('uploads/invoices/pdf');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = Str::random(8).'_'.time().'.pdf';
        $file->move($dir, $name);

        $batch = InvoiceBatch::create([
            'user_id' => 0,
            'original_filename' => $original,
            'pdf_path' => 'uploads/invoices/pdf/'.$name,
            'status' => 'pending',
            'model_used' => config('services.gemini.default_model'),
        ]);

        // Run inline (local test) — blocks until Gemini returns, then we redirect.
        $mode = $request->input('mode', config('services.gemini.default_mode', 'split'));
        app(InvoicePipeline::class)->run($batch, $dir.'/'.$name, null, null, $mode);

        return redirect('/local-invoices/'.$batch->id);
    }

    public function show($id)
    {
        $this->guard();
        $batch = InvoiceBatch::findOrFail($id);
        $invoices = $batch->invoices()->orderBy('page_number')->get();
        $sar = round((float) $batch->est_cost_usd * (float) config('services.gemini.usd_to_sar', 3.75), 4);

        return view('invoices_local.show', compact('batch', 'invoices', 'sar'));
    }
}
