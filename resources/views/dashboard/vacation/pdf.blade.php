<?php
use Elibyy\TCPDF\Facades\TCPDF;
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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
$pdf::SetPrintHeader(true);
$pdf::SetPrintFooter(true);
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
    $style = array(
'position' => '',
'align' => 'C',
'stretch' => false,
'fitwidth' => true,
'cellfitalign' => '',
'border' => false,
'hpadding' => 'auto',
'vpadding' => 'auto',
'fgcolor' => array(0,0,0),
'bgcolor' => false, //array(255,255,255),
'text' => true,
'font' => 'helvetica',
'fontsize' => 8,
'stretchtext' => 4
);

$style['position'] = 'C';
$pdf::write1DBarcode($ADDED_INFO_NO, 'C128A', '', '', '', 15, 0.4, $style, 'N');

$pdf::SetFont("xnahid", "B", 12);
$tile_txt='تقارير العمال';
$titile = '<p style="text-align:center;text-decoration:underline;">' . $tile_txt . '</p>';
$pdf::writeHTML($titile, true, false, false, false, '');
$pdf::Ln(4);

$pdf::SetFont("almohanad", "B", 10);

$tbl_header='';
$tbl_footer = '</table>';
$tbl = '';
$tbl_header='<table style="width:100%;background:#fff;table-layout: fixed;  border-collapse: collapse;"align="center"
border=1 bordercolor=#000000  cellspacing="0" cellpadding="4">
<tr nobr="true">
<td  nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:5%;">#</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">اسم العامل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">بداية الاجازة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">نهاية الاجازة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">عدد ايام الاجازة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">نوع الاجازة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">المسمى الوظيفي</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">مكان العمل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">ملاحظات</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">مدخل البيانات</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ الادخال</td>

</tr>';
$i=1;
$cn=0;
foreach ($list as $x) {

$worker_name = $x->worker_name;
$start = $x->start;
$end = $x->end;
$count_day = $x->count_day;
$vacation_type_name = $x->vacation_type_name;
$job_name = $x->job_name;
$work_place_name = $x->work_place_name;
$note = $x->note;
$name = $x->name;
$created_at = $x->created_at;







$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$x->worker_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$start.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$end.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$count_day.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$vacation_type_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$job_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$note.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>
</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




