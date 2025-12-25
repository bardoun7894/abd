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
$name="تقرير المحل";
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
$tile_txt='تقرير المحل';
$titile = '<p style="text-align:center;text-decoration:underline;">' . $tile_txt . '</p>';
$pdf::writeHTML($titile, true, false, false, false, '');
$pdf::Ln(4);

//$pdf::SetFont("almohanad", "B", 8);
$pdf::SetFont("almohanad", "B", 10);

$tbl_header='';
$tbl_footer = '</table>';
$tbl = '';
$tbl_header='<table style="width:100%;background:#fff;table-layout: fixed;  border-collapse: collapse;"align="center"
border=1 bordercolor=#000000  cellspacing="0" cellpadding="4">
<tr nobr="true">
<td  nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:5%;">#</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">اسم المحل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المجموعة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">اسم المسؤول</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المدينة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">رقم جوال المسؤول</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">موقع المحل</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">معلومات البلدية</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">معلومات السجل التجاري</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">معلومات الإيجار</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">معلومات الدفاع المدني</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ الادخال</td>

</tr>';
$i=1;
$cn=0;
$work_place_id= "";
$work_place_name= "";


	foreach ($list as $x) {
            $shop_name = $x->shop_name;
            $manager_name = $x->manager_name;
            $shop_respon = $x->shop_respon;
            $city_name = $x->city_name;
            $shop_mobile = $x->shop_mobile;
            $shop_location = $x->shop_location;
            $municip_no = $x->municip_no;
            $comme_no = $x->comme_no;
            $rent_no = $x->rent_no;
            $defence_no =  $x->defence_no;
            $created_at=$x->created_at;





$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_respon.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$city_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_mobile.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_location.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$municip_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$comme_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$rent_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$defence_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>


</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




