<?php
use Elibyy\TCPDF\Facades\TCPDF;

//$pdf = new TCPDF;
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
         //   $pdf::SetTitle('مستند إدخال لوازم');
            // set document information
            $pdf::SetCreator('ssss');

            $pdf::SetAuthor('Nicola Asuni');
            $pdf::SetTitle('مستند إدخال لوازم');
            $pdf::SetSubject('TCPDF Tutorial');
            $pdf::SetKeywords('TCPDF, PDF, example, test, guide');

            $pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 018', PDF_HEADER_STRING);

            $pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);



// set default header data
$pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf::setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf::setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf::setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf::SetFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
//$pdf::AddPage();

// set text shadow effect
$pdf::setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));















            $lg = Array();
            $lg['a_meta_charset'] = 'UTF-8';
            $lg['a_meta_dir'] = 'rtl';
            $lg['a_meta_language'] = 'fa';
            $lg['w_page'] = 'page';

            $pdf::setLanguageArray($lg);
            PDF::SetFont('time_n_r', '', 12);


            PDF::AddPage();
            PDF::setRTL(true);

?>
<!doctype html>
<html lag="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>

    </style>
</head>
<body>


<h2 style="font-family:'kufi'; text-align: center;font-weight: bold">مستند إدخال لوازم</h2>
<div>

    <table>
        <!--begin::Row-->
        <tr style="line-height: 3;">
            <td style="width: 70px">استلمت من:</td>
            <td style="font-family:'kufi';font-size:11px;line-height:4;text-decoration: underline;width: 180px">{{$worker->worker_id}}</td>
            <td style="width: 80px">رقم الفاتـورة:</td>
            <td style="font-family:'xyekan';font-size:18px;line-height:2.5;font-weight:bold;text-decoration: underline;">{{$worker->worker_id}}</td>
            <td style="width: 90px">تاريـــــخ:</td>
            <td style="font-family:'xyekan';font-size:18px;line-height:2.5;text-decoration: underline;">{{$worker->worker_id}}</td>
        </tr>
        <tr>
            <td style="width: 70px">الــعملـــة:</td>
            <td style="font-family:'kufi';font-size:11px;line-height:1.5;text-decoration: underline;">{{$worker->worker_id}}</td>
            <td style="width: 80px">سعر الصرف:</td>
            <td style="font-family:'xyekan';font-size:18px;line-height:1;text-decoration: underline;">{{$worker->worker_id}}</td>
            <td style="width: 90px">المبلغ الإجمالي :</td>
            <td style="font-family:'xyekan';font-size:18px;line-height:1;text-decoration: underline;">{{$worker->worker_id}} <span style="font-family:'kufi';font-size:11px;;">{{$worker->worker_id}}</span></td>
        </tr>

    </table>

</div>
<div style="border-bottom:1px solid #000;">
    <table style="line-height: 2;" nobr="true">

        <thead>
        <tr style="font-weight: bold;border:1px solid #cccccc;background-color:#f2f2f2;">

            <th style="font-family:'kufi';font-size:11px;line-height:3;text-align:center;border:1px solid #cccccc;width: 70px">رقم الصنف</th>


        </tr>
        <!--end::Table row-->
        </thead>
        <!--end::Table head-->
        <!--begin::Table body-->
        <tbody>
<tr>
<td>ddddddddd</td>
</tr>
        </tbody>

    </table>
</div>
<div>
    <table>
        <!--begin::Row-->
        <tr style="line-height: 3;">
            <td style="width: 300px">استلمت اللوازم المذكورة أعلاه حسب الأصول</td>
            <td style="width: 200px">اسم المستلم/ أمين المستودع </td>
            <td style="text-decoration: underline;">{{Auth::user()->name}}</td>
        </tr>
        <tr>
            <td style="width: 80px">بتاريخ:</td>
            <td style="text-decoration: underline;width: 220px">{{Auth::user()->name}}</td>
            <td style="width: 50px">دائرة/ </td>
            <td style="text-decoration: underline;width: 150px"> اللوازم والمخازن</td>
            <td style="width: 50px">التوقيع:</td>
            <td></td>
        </tr>

    </table>


</body>
</html>










