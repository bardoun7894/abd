<?php
use Elibyy\TCPDF\Facades\TCPDF;
use App\Support\ArabicNumberToWords;

/**
 * Printable سند قبض / سند صرف — Spec 008 bundle 1 (cashbox), redesigned.
 *
 * DESIGN NOTES
 * ------------
 * Built entirely with writeHTML tables rather than TCPDF's drawing API. The
 * drawing API needs absolute coordinates, and TCPDF mirrors x for Cell() but
 * NOT for Image() under setRTL(true) — mixing the two is where RTL PDFs go
 * wrong. The HTML engine handles RTL consistently, so the whole voucher is one
 * HTML pass; the only direct API calls left are the VOID watermark transform.
 *
 * Palette — the app's emerald structure (so a printed سند matches the screen it
 * came from) with the LOGO's amber sunrise as the single accent. Sampled from
 * public/assets/media/logos/logo.jpg: teal-green #00C090, amber #E0A020,
 * orange #F08010. The deep emerald sits comfortably beside the logo's teal
 * while staying identical to --sn-emerald in public/css/app-ui.css.
 *
 * Content follows the conventional Arabic/Gulf receipt-voucher layout: serial
 * number, date, payer, amount in FIGURES AND WORDS (تفقيط — the standard guard
 * against tampering), the reference it settles, and signature lines.
 * Deliberately NOT printed: a Hijri date (the app ships no converter, and a
 * wrong date on a financial document is worse than no date) and a payment
 * method (never captured, so it would have to be invented).
 *
 * Font: 'aealarabiya' — TCPDF's own bundled Arabic font, confirmed present
 * under vendor/tecnickcom/tcpdf/fonts/. Do not swap it for 'almohanad'/'xnahid'
 * referenced elsewhere in the app's legacy PDFs; those files are absent.
 */

/* ---- brand tokens ------------------------------------------------------- */
$EMERALD      = '#0e6b4f';   // --sn-emerald
$EMERALD_DEEP = '#0a4f3a';   // --sn-emerald-deep
$TINT         = '#e4efe9';   // --sn-emerald-tint
$LINE         = '#cbd5d1';
$INK          = '#1a2b25';
$MUTED        = '#5b6b64';
$AMBER        = '#e0a020';   // logo — wheat / sunrise accent
$DANGER       = '#a01515';

$isVoid = (int) ($receipt->is_void ?? 0) === 1;
$isIn = ($receipt->direction ?? 'in') === 'in';
$docTitle = $isIn ? 'سند قبض' : 'سند صرف';
$amountLabel = $isIn ? 'المبلغ المستلم' : 'المبلغ المصروف';

$amount = (float) ($receipt->amount ?? 0);
$amountWords = ArabicNumberToWords::amount($amount);

/*
 * logo-voucher.png is logo.jpg with its surrounding padding trimmed and
 * flattened onto white: the raw file carries a wide near-white margin that
 * prints as a visible grey rectangle beside the company name at masthead size.
 *
 * It lives under resources/ and NOT next to the original, because
 * public/assets is gitignored — a derived asset placed there would never reach
 * the server. TCPDF reads it straight off the filesystem, so it does not need
 * to be web-served. Falls back to the original public logo if it is ever
 * missing; the company name is typed beside the mark either way, so the
 * masthead can never come out blank.
 */
$logo = collect([
    resource_path('images/logo-voucher.png'),
    public_path('assets/media/logos/logo.jpg'),
])->first(fn ($p) => is_file($p));
$hasLogo = $logo !== null;

$fmtDate = function ($v) {
    if (empty($v)) {
        return null;
    }
    try {
        return \Carbon\Carbon::parse($v)->format('d-m-Y');
    } catch (\Throwable $e) {
        return (string) $v;
    }
};

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf::SetAuthor('شركة صباح النور');
$pdf::SetTitle($docTitle . ' ' . ($receipt->receipt_no ?? ''));
$pdf::SetPrintHeader(false);
$pdf::SetPrintFooter(false);
$pdf::SetMargins(12, 12, 12);
$pdf::SetAutoPageBreak(true, 14);
$lg = ['a_meta_charset' => 'UTF-8', 'a_meta_dir' => 'rtl', 'a_meta_language' => 'ar', 'w_page' => 'صفحة'];
$pdf::setLanguageArray($lg);
$pdf::AddPage('P', 'A4');
$pdf::setRTL(true);
$pdf::SetFont('aealarabiya', '', 11);

/* ---- helpers ------------------------------------------------------------- */

/** One label/value row. Returns '' for an empty value, so a non-lease receipt
 *  prints a shorter but still complete voucher instead of a row of dashes. */
$row = function (string $label, $value) use ($TINT, $LINE, $INK, $MUTED) {
    if ($value === null || $value === '') {
        return '';
    }

    return '<tr>'
        . '<td width="32%" style="background-color:' . $TINT . ';border:0.6px solid ' . $LINE . ';color:' . $MUTED . ';font-weight:bold;">' . e($label) . '</td>'
        . '<td width="68%" style="border:0.6px solid ' . $LINE . ';color:' . $INK . ';">' . e($value) . '</td>'
        . '</tr>';
};

/* ---- 1. masthead: logo + company, document type --------------------------
 * The logo is sized in points here (~62pt ≈ 22mm). TCPDF reserves the declared
 * height as real cell height, so an oversized value silently pushes the whole
 * voucher down the page — that is what a first pass at 150 did. The company
 * name is always typed alongside it, so the identity survives even if the
 * image asset is ever missing or fails to decode. */
$html = '<table cellpadding="0" style="width:100%;">'
    . '<tr>'
    . '<td width="14%" style="vertical-align:middle;">'
    . ($hasLogo ? '<img src="' . $logo . '" width="62" height="62" />' : '')
    . '</td>'
    . '<td width="46%" style="vertical-align:middle;">'
    . '<span style="font-size:15px;font-weight:bold;color:' . $EMERALD_DEEP . ';">شركة صباح النور</span><br />'
    . '<span style="font-size:8px;color:' . $MUTED . ';">SABAH ALNOOR CO.</span>'
    . '</td>'
    . '<td width="40%" style="vertical-align:middle;text-align:left;">'
    . '<span style="font-size:21px;font-weight:bold;color:' . $EMERALD . ';">' . $docTitle . '</span><br />'
    . '<span style="font-size:8px;color:' . $MUTED . ';">' . ($isIn ? 'RECEIPT VOUCHER' : 'PAYMENT VOUCHER') . '</span>'
    . '</td>'
    . '</tr>'
    . '</table>';

// Sunrise rule — the logo's amber, the document's single accent.
$html .= '<table cellpadding="0" style="width:100%;"><tr>'
    . '<td style="background-color:' . $AMBER . ';height:3px;line-height:3px;font-size:1px;">&nbsp;</td>'
    . '</tr></table>';

/* ---- 2. serial + date strip ---------------------------------------------- */
$html .= '<table cellpadding="7" style="width:100%;">'
    . '<tr>'
    . '<td width="50%" style="background-color:' . $EMERALD . ';color:#ffffff;font-weight:bold;font-size:12px;">'
    . 'رقم السند: ' . e($receipt->receipt_no ?? '—')
    . '</td>'
    . '<td width="50%" style="background-color:' . $EMERALD_DEEP . ';color:#ffffff;font-weight:bold;font-size:12px;text-align:left;">'
    . 'التاريخ: ' . e($fmtDate($receipt->receipt_date ?? null) ?? '—')
    . '</td>'
    . '</tr>'
    . '</table>';

/* ---- 3. amount + تفقيط ---------------------------------------------------- */
$html .= '<br /><table cellpadding="9" style="width:100%;border:1.2px solid ' . $EMERALD . ';">'
    . '<tr>'
    . '<td width="38%" style="background-color:' . $TINT . ';color:' . $EMERALD_DEEP . ';font-weight:bold;font-size:12px;">' . $amountLabel . '</td>'
    . '<td width="62%" style="font-size:19px;font-weight:bold;color:' . $EMERALD . ';">'
    . number_format($amount, 2) . ' <span style="font-size:10px;color:' . $MUTED . ';">ريال</span>'
    . '</td>'
    . '</tr>';

if ($amountWords) {
    $html .= '<tr>'
        . '<td colspan="2" style="border-top:0.6px solid ' . $LINE . ';color:' . $INK . ';font-size:10px;">'
        . '<span style="color:' . $MUTED . ';">تفقيط: </span>' . e($amountWords)
        . '</td>'
        . '</tr>';
}
$html .= '</table>';

/* ---- 4. البيان ------------------------------------------------------------ */
$period = null;
if (! empty($receipt->period_from) || ! empty($receipt->period_to)) {
    $period = ($fmtDate($receipt->period_from ?? null) ?? '—') . '  إلى  ' . ($fmtDate($receipt->period_to ?? null) ?? '—');
}

$paymentNo = null;
if (! empty($receipt->payment_no)) {
    // payment_no is stored as a bare ordinal so it stays sortable/queryable —
    // "n من m" is composed here, at print time, from payment_total.
    $paymentNo = ! empty($receipt->payment_total)
        ? $receipt->payment_no . ' من ' . $receipt->payment_total
        : $receipt->payment_no;
}

$details = $row('اسم المحل', $receipt->shop_name ?? null)
    . $row('رقم العقد', $receipt->contract_no ?? null)
    . $row('رقم الدفعة', $paymentNo)
    . $row('الفترة المستحقة', $period)
    . $row($isIn ? 'اسم الدافع' : 'المستفيد', $receipt->payer_name ?? null)
    . $row($isIn ? 'استلمه' : 'صرفه', $receivedByName ?? null)
    . $row('المرجع', trim(($receipt->source_type ?? '') . ' #' . ($receipt->source_id ?? '')))
    . $row('ملاحظة', $receipt->note ?? null);

$html .= '<br /><table cellpadding="7" style="width:100%;">'
    . '<tr><td colspan="2" style="background-color:' . $EMERALD_DEEP . ';color:#ffffff;font-weight:bold;font-size:11px;">البيان</td></tr>'
    . $details
    . '</table>';

/* ---- 5. void banner ------------------------------------------------------- */
if ($isVoid) {
    $html .= '<br /><table cellpadding="8" style="width:100%;border:1px solid ' . $DANGER . ';">'
        . '<tr><td width="28%" style="background-color:#fdecec;color:' . $DANGER . ';font-weight:bold;">سند ملغى — السبب</td>'
        . '<td width="72%" style="color:' . $DANGER . ';">' . e($receipt->void_reason ?? '—') . '</td></tr>'
        . '</table>';
}

/* ---- 6. signatures -------------------------------------------------------- */
$sig = function (string $label) use ($LINE, $MUTED) {
    return '<td width="33%" style="text-align:center;color:' . $MUTED . ';font-size:9px;">'
        . $label . '<br /><br /><br />'
        . '<span style="color:' . $LINE . ';">............................</span>'
        . '</td>';
};

$html .= '<br /><br /><table cellpadding="4" style="width:100%;"><tr>'
    . $sig($isIn ? 'توقيع الدافع' : 'توقيع المستلم')
    . $sig('أمين الصندوق')
    . $sig('المدير المالي')
    . '</tr></table>';

/* ---- 7. footer ------------------------------------------------------------ */
$html .= '<br /><table cellpadding="0" style="width:100%;"><tr>'
    . '<td style="background-color:' . $LINE . ';height:1px;line-height:1px;font-size:1px;">&nbsp;</td>'
    . '</tr></table>'
    . '<table cellpadding="4" style="width:100%;"><tr>'
    . '<td width="58%" style="font-size:8px;color:' . $MUTED . ';">شركة صباح النور — Sabah Alnoor CO. · سند صادر إلكترونياً من النظام المالي</td>'
    . '<td width="42%" style="font-size:8px;color:' . $MUTED . ';text-align:left;">تاريخ الطباعة: ' . \Carbon\Carbon::now()->format('d-m-Y H:i') . '</td>'
    . '</tr></table>';

$pdf::writeHTML($html, true, false, true, false, '');

/* ---- 8. VOID watermark ---------------------------------------------------- */
if ($isVoid) {
    $pdf::SetAlpha(0.25);
    $pdf::SetFont('aealarabiya', 'B', 58);
    $pdf::SetTextColor(160, 21, 21);
    $pdf::StartTransform();
    $pdf::Rotate(45, 105, 150);
    $pdf::Text(58, 150, 'ملغى / VOID');
    $pdf::StopTransform();
    $pdf::SetAlpha(1);
}
