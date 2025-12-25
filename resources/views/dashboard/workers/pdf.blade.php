<?php
use Elibyy\TCPDF\Facades\TCPDF;
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf::SetAuthor('Mazayz');
$pdf::SetTitle('Join Report');
$pdf::SetSubject('Join Report details');
$pdf::SetKeywords('Purchase, Sale, Order, Payment');
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::setHeaderFont(Array('andlso', '', 13));
$name="تقرير العمال";
$pdf::SetTitle($name);
$pdf::SetMargins(5,30,5);
$pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf::SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf::SetPrintHeader(false);
$pdf::SetPrintFooter(false);
$lg = Array();
$lg['a_meta_charset'] = 'UTF-8';
$lg['a_meta_dir'] = 'rtl';
$lg['a_meta_language'] = 'ar';
$lg['w_page'] = 'page';
$pdf::setLanguageArray($lg);
$pdf::AddPage('L', 'A4');
$pdf::SetFont('dejavusans', '', 14, '', true);
PDF::setRTL(true);
$z2 = count($list);
if($z2==0){
$work_place_id= "";
$work_place_name= "";
}
else{
$pdf::SetFont("xnahid", "B", 12);
$tile_txt='تقارير العمال';
$titile = '<p style="text-align:center;text-decoration:underline;">' . $tile_txt . '</p>';
$pdf::writeHTML($titile, true, false, false, false, '');
$pdf::Ln(2);
$pdf::SetFont("almohanad", "B", 10);

$tbl_header='';
$tbl_footer = '</table>';
$tbl = '';
$tbl_header='<table style="width:100%;background:#fff;table-layout: fixed;  border-collapse: collapse;"align="center"
border=1 bordercolor=#000000  cellspacing="0" cellpadding="4">
<tr nobr="true">
<td  nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:5%;">#</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">اسم العامل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ الميلاد</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">رقم الإقامة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">المجموعة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريخ اصدار الاقامة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريخ إنتهاء الإقامة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريخ انتهاء الجواز</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">الجنسية</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريخ التعيين</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">مكان العمل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المهنة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">التواجد</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريخ الادخال</td>
</tr>';
$i=1;
$cn=0;
$work_place_id= "";
$work_place_name= "";
foreach ($list as $x) {
$work_place_id= $x->work_place_id;
$work_place_name = $x->work_place_name;
$manager_name = $x->manager_name;
$dob = $x->dob;
if($x->doe_desc=='3'){
$doe_desc_char='شارف على الانتهاء';
}
else if($x->doe_desc=='2'){
$doe_desc_char='منتهي';
}else if($x->doe_desc=='1'){
$doe_desc_char='سارية';
}else{
$doe_desc_char='غير مدخل';
}
if($x->dop_desc=='3'){
$dop_desc_char='شارف على الانتهاء';
}
else if($x->dop_desc=='2'){
$dop_desc_char='منتهي';
}else if($x->dop_desc=='1'){
$dop_desc_char='سارية';
}else{
$dop_desc_char='غير مدخل';
}
if($x->inside==1){
$inside_desc='داخل المملكة';
}
else{
$inside_desc='خارج المملكة';
}
$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">
    <img src="'.$x->avatar.'" alt="صورة العامل" style="width:75px;height:75px;border-radius:50%;"><br>
    '.$x->worker_name.'
</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->dob.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->ssn.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->dos.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->doe.'<br>'.$doe_desc_char.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->dop.'<br>'.$dop_desc_char.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->nation_name_ar.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->dow.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->job_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$inside_desc.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->created_at.'</td>
</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
