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

            $z2 = count($list);
if($z2==0){
    $work_place_id= "";
$work_place_name= "";

}
else{

    $pdf::SetFont("almohanad", "B", 12);

            $tile_txt='تقرير المشتريات';
            $titile = '<p style="text-align:center;text-decoration:underline;">' . $tile_txt . '</p>';
	$pdf::writeHTML($titile, true, false, false, false, '');
	$pdf::Ln(4);

    $tbl_header='';
$tbl_footer = '</table>';
$tbl = '';
$tbl_header='<table style="width:100%;background:#fff;table-layout: fixed;  border-collapse: collapse;"align="center"
border=1 bordercolor=#000000  cellspacing="0" cellpadding="4">
<tr nobr="true">
<td  nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:6%;">#</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">التصنيف</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">تفصيل المنتج</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">خصم من حساب</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">تاريخ</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:11%;">الكمية</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:11%;">السعر</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">الاجمالي</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">ملاحظات</td>

</tr>';
$i=1;
$cn=0;
$work_place_id= "";
$work_place_name= "";


	foreach ($list as $x) {
$work_place_id= $x->work_place_id;
$work_place_name = $x->work_place_name;
$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>
<td style="text-align:center;border: 1px solid #000000;font-weight: bold;">'.$work_place_name.'</td>


</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




