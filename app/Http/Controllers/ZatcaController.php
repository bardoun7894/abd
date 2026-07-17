<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * ZATCA Phase-2 (Fatoora clearance/reporting) — NOT yet activated.
 *
 * Phase-1 (the simplified-invoice QR) is fully implemented elsewhere:
 * see App\Services\ZatcaQrGenerator + Dashboard\InvoiceController.
 *
 * Phase-2 (UBL 2.1 XML + cryptographic stamp + submission to the Fatoora
 * clearance/reporting API) is BLOCKED on ZATCA onboarding — the business's CR
 * + OTP from the Fatoora portal must be exchanged for a compliance CSID and a
 * production CSID/certificate before anything can be transmitted. Those secrets
 * belong in the server .env / Settings, never in source.
 *
 * NOTE: an earlier version of this file hardcoded a sandbox OTP and a CSR/secret
 * directly in the code and exposed them on an unauthenticated GET route. Those
 * values are considered COMPROMISED and must be rotated in the ZATCA portal.
 */
class ZatcaController extends Controller
{
    public function index()
    {
        if (! Auth::check() || (int) (Auth::user()->emp_job ?? 0) !== 1) {
            abort(403, 'هذه الصفحة مخصّصة لمدير النظام فقط');
        }

        return response()->json([
            'status' => false,
            'phase' => 2,
            'message_out' => 'ربط الفوترة الإلكترونية (المرحلة الثانية) مع هيئة الزكاة والضريبة غير مفعّل بعد. '
                .'يلزم إتمام التسجيل في منصة فاتورة (السجل التجاري + رمز OTP) للحصول على شهادة CSID قبل الإرسال. '
                .'أما رمز QR (المرحلة الأولى) فهو مفعّل على الفواتير.',
        ], 501);
    }
}
