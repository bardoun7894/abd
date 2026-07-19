<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInteractiveExtraction;
use App\Models\AiExtractionJob;
use App\Services\AiSubscriptionGate;
use App\Services\AuditLogger;
use App\Services\DocumentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Generic async document extraction (P2 — extend async to every extractor).
 *
 * `start(module)` stores the upload, queues ProcessInteractiveExtraction, and returns
 * a job id immediately so the form never blocks on Gemini. `status(job)` polls it.
 * One endpoint for worker/expense/manager/vehicle (and shop) instead of a copy of the
 * shop methods per controller. The extractor per module lives in the job's map.
 */
class AiExtractionController extends Controller
{
    /** Modules the async job knows how to extract (must match ProcessInteractiveExtraction::EXTRACTORS). */
    private const MODULES = ['shop', 'worker', 'expense', 'manager', 'vehicle'];

    public function start(Request $request, string $module)
    {
        if (! in_array($module, self::MODULES, true)) {
            return response()->json(['status' => false, 'message_out' => 'وحدة غير معروفة'], 404);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:20480',
        ]);

        // Fail fast if the AI subscription is blocked (before storing/queueing).
        try {
            app(AiSubscriptionGate::class)->assertAllowed();
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => $e->getMessage()], 422);
        }

        $ds = app(DocumentStorage::class);
        $stored = $ds->store($request->file('document'), $module);
        $fileUrl = route('dashboard.documents.serve', ['module' => $module, 'filename' => $stored['filename']]);

        $job = AiExtractionJob::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'status' => 'pending',
            'file_path' => $stored['filename'],
            'file_url' => $fileUrl,
        ]);

        ProcessInteractiveExtraction::dispatch($job->id);

        AuditLogger::log($module, null, AuditLogger::EXTRACT, [
            'note' => 'استخراج مستند بالذكاء الاصطناعي (غير متزامن)',
        ]);

        return response()->json(['status' => true, 'job_id' => $job->id]);
    }

    /** Poll an async extraction job; returns state + (when done) the extracted fields. */
    public function status($jobId)
    {
        $job = AiExtractionJob::find($jobId);
        if (! $job || ($job->user_id && (int) $job->user_id !== (int) Auth::id() && (int) (Auth::user()->emp_job ?? 0) !== 1)) {
            return response()->json(['status' => false, 'message_out' => 'الطلب غير موجود'], 404);
        }

        $data = null;
        if ($job->status === 'done') {
            // Return the extractor's full normalized output so each module's form reads the
            // fields it needs, plus a couple of shared conveniences.
            $d = $job->result_json ?? [];
            $data = array_merge($d, [
                'confidence' => $d['field_confidence'] ?? [],
                'document_url' => $job->file_url,
            ]);
        }

        return response()->json([
            'status' => true,
            'state' => $job->status, // pending | processing | done | failed
            'data' => $data,
            'error' => $job->status === 'failed' ? ($job->error ?: 'فشل الاستخراج') : null,
        ]);
    }
}
