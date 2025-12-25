<?php
use Elibyy\TCPDF\Facades\TCPDF;



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




/*

$pdf::setemp_name($xx);
$pdf::setCompany_name_ar($comp_name_ar);
$pdf::setCompany_name_en($comp_name_en);
$pdf::settax_no_ar($tax_no_ar);
$pdf::setcomp_name_en($tax_no_en);
$pdf::setcomp_det_ar($comp_det_ar);
$pdf::setcomp_det_en($comp_det_en);*/


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

//$pdf::SetFont("almohanad", "B", 8);
$pdf::SetFont("almohanad", "B", 8);

$tbl_header='';
$tbl_footer = '</table>';
$tbl = '';
$tbl_header='<table style="width:100%;background:#fff;table-layout: fixed;  border-collapse: collapse;"align="center"
border=1 bordercolor=#000000  cellspacing="0" cellpadding="4">
<tr nobr="true">
<td  nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:6%;">#</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">اسم العامل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">رقم الإقامة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">تاريخ اصدار الاقامة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">تاريخ إنتهاء الإقامة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:11%;">تاريخ انتهاء الجواز</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:11%;">الجنسية</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ التعيين</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">مكان العمل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المهنة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">التواجد</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ الادخال</td>

</tr>';
$i=1;
$cn=0;
$work_place_id= "";
$work_place_name= "";


	foreach ($list as $x) {
$work_place_id= $x->work_place_id;
$work_place_name = $x->work_place_name;
$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$work_place_name.'</td>


</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




