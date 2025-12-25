<?php
use Elibyy\TCPDF\Facades\TCPDF;

//$pdf = new TCPDF;
// create new PDF document






class MYPDF extends TCPDF {
/*public function setemp_name($var_2){$this->setemp_name = $var_2;}
public function setCompany_name_ar($comp_name_ar){$this->comp_name_ar = $comp_name_ar;}
public function setCompany_name_en($comp_name_en){$this->comp_name_en = $comp_name_en;}
public function settax_no_ar($tax_no_ar){$this->tax_no_ar = $tax_no_ar;}
public function setcomp_name_en($tax_no_en){$this->tax_no_en = $tax_no_en;}
public function setcomp_det_ar($comp_det_ar){$this->comp_det_ar = $comp_det_ar;}
public function setcomp_det_en($comp_det_en){$this->comp_det_en = $comp_det_en;}*/
/*
$setemp_name = 'sssssssssssss';
$comp_name_ar = 'ssssssssssssssssss';
$comp_name_en = 'ssssssssssssssssss';
$tax_no_ar = 'ssssssssssssssssss';
$tax_no_en = 'ssssssssssssssssss';
$comp_det_ar = 'ssssssssssssssssss';
$comp_det_en = 'ssssssssssssssssss';*/

/*public function Header() {
$image_file = K_PATH_IMAGES.'logo_jp.jpeg';
$pdf->Image('@'.file_get_contents( K_PATH_IMAGES.'logo_jp.jpeg'), 125, 5, 25, '', 'jpeg', '', '}', false, 10, '', false, false, 0, false, false, false);
$pdf->SetFont("time_n_r", "", 9);
$today = date("Y-m-d");
$html = '<strong>'.$this->comp_name_ar.'</strong><br/>
'.$this->comp_det_ar.'<br/>
'.$this->tax_no_ar.'<br/>';
$this->writeHTMLCell($w=0,$h=10,$x=10,$y=8,$html,$border=0,$ln=0,$fill=false,$reseth=true,$align='R');
$html = '<strong>'.$this->comp_name_en.'</strong><br/>'.$this->comp_det_en.'<br/>'.$this->tax_no_en.'<br/>';
$this->writeHTMLCell($w=0,$h=10,$x=10,$y=8,$html,$border=0,$ln=0,$fill=false,$reseth=true,$align='L');
}*/
public function Footer() {
$footertext="تمت طباعة التقرير بواسطة الموظف  :  "
."التاريخ : ". date("Y-m-d").'                     '
."الوقت : ". date("h:i:sa").'                     '
."ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع ".'                     ';
$pdf->SetY(-15);
$pdf->SetFont('almohanad', 'B', 9);
$pdf->Cell(0, 10,  $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');
}


}










$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
/*
$pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 018', PDF_HEADER_STRING);
$pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf::setFooterData(array(0,64,0), array(0,64,128));
$pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);*/








$pdf::SetAuthor('Mazayz');
$pdf::SetTitle('Join Report');
$pdf::SetSubject('Join Report details');
$pdf::SetKeywords('Purchase, Sale, Order, Payment');
$pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::setHeaderFont(Array('andlso', '', 13));
$name="تقرير العملاء";
$pdf::SetTitle($name);

$pdf::SetMargins(5,40,5);
$pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf::SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf::SetPrintHeader(false);
$pdf::SetPrintFooter(true);


$lg = Array();
$lg['a_meta_charset'] = 'UTF-8';
$lg['a_meta_dir'] = 'rtl';
$lg['a_meta_language'] = 'ar';
$lg['w_page'] = 'page';
$pdf::setLanguageArray($lg);
$pdf::AddPage('P', 'A4');
$pdf::SetFont('dejavusans', '', 14, '', true);
PDF::setRTL(true);







//$pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//$pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
/*if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf::setLanguageArray($l);
}*/

// ---------------------------------------------------------

//$pdf::setFontSubsetting(true);

//$pdf::SetFont('dejavusans', '', 14, '', true);

//$pdf::setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));















         /*   $lg = Array();
            $lg['a_meta_charset'] = 'UTF-8';
            $lg['a_meta_dir'] = 'rtl';
            $lg['a_meta_language'] = 'fa';
            $lg['w_page'] = 'page';

            $pdf::setLanguageArray($lg);
            PDF::SetFont('time_n_r', '', 12);


            PDF::AddPage();
            PDF::setRTL(true);*/

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
        </thead>
        <tbody>
<tr>
<td>ddddddddd</td>
</tr>
        </tbody>

    </table>
</div>
<div>
    <table>
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










