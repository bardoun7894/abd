<?php
use Elibyy\TCPDF\Facades\TCPDF;

/**
 * Printable سند قبض voucher — Spec 008 bundle 1 (cashbox). Mirrors the
 * established house pattern in dashboard/purchase/pdf.blade.php: a fresh
 * TCPDF instance is created here and PDF::Output() is called from the
 * controller afterwards against the same underlying singleton.
 *
 * Font: 'aealarabiya' — confirmed bundled under
 * vendor/tecnickcom/tcpdf/fonts/aealarabiya.* — TCPDF's own Arabic font,
 * unlike 'almohanad'/'xnahid' referenced (unverified/absent) elsewhere in the
 * app's legacy PDF views.
 */
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf::SetAuthor('شركة صباح النور');
$pdf::SetTitle('سند قبض ' . ($receipt->receipt_no ?? ''));
$pdf::SetPrintHeader(false);
$pdf::SetPrintFooter(false);
$pdf::SetMargins(15, 15, 15);
$lg = [];
$lg['a_meta_charset'] = 'UTF-8';
$lg['a_meta_dir'] = 'rtl';
$lg['a_meta_language'] = 'ar';
$lg['w_page'] = 'صفحة';
$pdf::setLanguageArray($lg);
$pdf::AddPage('P', 'A4');
$pdf::setRTL(true);
$pdf::SetFont('aealarabiya', '', 12);

$isVoid = (int) ($receipt->is_void ?? 0) === 1;

$directionLabel = ($receipt->direction ?? 'in') === 'in' ? 'سند قبض' : 'سند صرف';

$html = '<div style="text-align:center;">'
    . '<h2>شركة صباح النور</h2>'
    . '<h3>' . $directionLabel . '</h3>'
    . '</div>'
    . '<table style="width:100%;border-collapse:collapse;" cellpadding="6">'
    . '<tr><td style="width:30%;font-weight:bold;border:1px solid #000;">رقم السند</td><td style="border:1px solid #000;">' . e($receipt->receipt_no) . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">التاريخ</td><td style="border:1px solid #000;">' . e(\Carbon\Carbon::parse($receipt->receipt_date)->format('d-m-Y')) . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">المبلغ</td><td style="border:1px solid #000;">' . number_format((float) $receipt->amount, 2) . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">اسم الدافع</td><td style="border:1px solid #000;">' . e($receipt->payer_name ?? '') . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">استلمه</td><td style="border:1px solid #000;">' . e($receivedByName ?? '') . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">نوع المصدر</td><td style="border:1px solid #000;">' . e($receipt->source_type) . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">رقم المرجع</td><td style="border:1px solid #000;">' . e($receipt->source_id) . '</td></tr>'
    . '<tr><td style="font-weight:bold;border:1px solid #000;">ملاحظة</td><td style="border:1px solid #000;">' . e($receipt->note ?? '') . '</td></tr>'
    . '</table>';

if ($isVoid) {
    $html .= '<br/><table style="width:100%;border-collapse:collapse;" cellpadding="6">'
        . '<tr><td style="font-weight:bold;border:1px solid #a00;background:#fdd;">سبب الإلغاء</td><td style="border:1px solid #a00;background:#fdd;">' . e($receipt->void_reason ?? '') . '</td></tr>'
        . '</table>';
}

$pdf::writeHTML($html, true, false, true, false, 'J');

if ($isVoid) {
    $pdf::SetAlpha(0.3);
    $pdf::SetFont('aealarabiya', 'B', 60);
    $pdf::SetTextColor(200, 0, 0);
    $pdf::StartTransform();
    $pdf::Rotate(45, 105, 150);
    $pdf::Text(60, 150, 'ملغى / VOID');
    $pdf::StopTransform();
    $pdf::SetAlpha(1);
}
