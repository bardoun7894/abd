<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\DocumentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Spec 005 T-C1 — additive, opt-in authenticated serve route for AI-document
 * uploads stored via App\Services\DocumentStorage. Decrypts on the fly when
 * the stored document is encrypted, and transparently serves legacy
 * plaintext files (public/uploads/**) for backward compatibility.
 *
 * This route is NOT wired into any existing AI upload flow yet — a
 * controller only needs to link to it once it is migrated to store via
 * DocumentStorage::store() instead of File::move() into public_path().
 */
class DocumentController extends Controller
{
    public function serve(Request $request, string $module, string $filename)
    {
        if (! Auth::check()) {
            abort(403, 'غير مصرح لك بعرض هذا المستند');
        }

        try {
            $doc = app(DocumentStorage::class)->read($module, $filename);
        } catch (\Throwable $e) {
            abort(404, 'المستند غير موجود');
        }

        AuditLogger::log($module, null, AuditLogger::READ, [
            'note' => 'عرض مستند ذكاء اصطناعي: '.$filename,
        ]);

        return response($doc['contents'], 200, [
            'Content-Type' => $doc['mime'],
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
