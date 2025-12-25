<?php
ob_start();
use Carbon\Carbon;

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
$pdf::SetMargins(5,30,5);
$pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf::SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf::SetPrintHeader(true);
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




            $rentpay_dt=$x->rentpay_dt;
                if($rentpay_dt==''){
                    $rentpay_dt_char = 'يحتاج الى تحديث';
                }

                else{



                $rentpay_dt =   Carbon::parse($rentpay_dt)->format('d-m-Y');
                $today =   Carbon::parse(now())->format('d-m-Y');
                $rentpay_price=$x->rentpay_price;


                $newDateTime = Carbon::now()->addDays(30);
                $newDateTime = Carbon::parse($newDateTime)->format('d-m-Y');


                $today = date("Y-m-d", strtotime($today));
                $rentpay_dt = date("Y-m-d", strtotime($rentpay_dt));
                $newDateTime = date("Y-m-d", strtotime($newDateTime));



                if ($rentpay_dt > $today and  $rentpay_dt > $newDateTime) {
                    $rentpay_dt_char = $rentpay_dt.'ساري'. $x->rentpay_price;
                }
              else  if ($rentpay_dt > $today and  $rentpay_dt < $newDateTime) {
                    $rentpay_dt_char = $rentpay_dt.'على وشك الاستحقاق'.$x->rentpay_price;
                }

                else if ($rentpay_dt == $today) {
                    $rentpay_dt_char = $rentpay_dt.'مستحق الان'. $x->rentpay_price;
                }
                else{
                    $rentpay_dt_char = 'يحتاج الى تحديث';

                }
            }






                if ($x->sm_desc == '3') {
                    $sm_desc_char = '<br>شارف على الانتهاء';
                } else if ($x->sm_desc == '2') {
                    $sm_desc_char = '<br>منتهي';
                } else if ($x->sm_desc == '1') {
                    $sm_desc_char = '<br>سارية';
                } else {
                    $sm_desc_char = '<br>غير مدخل';
                }
                $sm_desc_txt = '';



                if ($x->municip_no != '') {
                    $sm_desc_txt = '' . $x->municip_no . '';
                }
                if ($x->municip_edt != '') {
                    $sm_desc_txt = '' . $x->municip_no . '<br>';
                    $sm_desc_txt .=  $x->municip_edt  . $sm_desc_char ;
                }


                if ($x->shel_desc == '3') {
                    $shel_desc_char = '<br>شارف على الانتهاء';
                } else if ($x->shel_desc == '2') {
                    $shel_desc_char = '<br>منتهي';
                } else if ($x->shel_desc == '1') {
                    $shel_desc_char = '<br>سارية';
                } else {
                    $shel_desc_char = '<br>غير مدخل';
                }
                $shel_desc_txt = '';



                if ($x->health_no != '') {
                    $shel_desc_txt = '' . $x->health_no . '';
                }
                if ($x->health_edt != '') {
                    $shel_desc_txt .= '' . $x->health_no . '<br>';
                    $shel_desc_txt .= '' . $x->health_edt . '' . $shel_desc_char . '';
                }

                if ($x->sd_desc == '3') {
                    $sd_desc_char = '<br>شارف على الانتهاء';
                } else if ($x->sd_desc == '2') {
                    $sd_desc_char = '<br>منتهي';
                } else if ($x->sd_desc == '1') {
                    $sd_desc_char = '<br>سارية';
                } else {
                    $sd_desc_char = '<br>غير مدخل';
                }
                $sd_desc_txt = '';


                if ($x->defence_no != '') {
                    $sd_desc_txt = '' . $x->defence_no ;
                }
                if ($x->defence_edt != '') {
                    $sd_desc_txt = '' . $x->defence_no.'<br>' ;

                    $sd_desc_txt .=  $x->defence_edt .$sd_desc_char ;
                }
                if ($x->sc_desc == '3') {
                    $sc_desc_char = '<br>شارف على الانتهاء';
                } else if ($x->sc_desc == '2') {
                    $sc_desc_char = '<br>منتهي';
                } else if ($x->sc_desc == '1') {
                    $sc_desc_char = '<br>سارية';
                } else {
                    $sc_desc_char = '<br>غير مدخل';
                }
                $sc_desc_txt = '';


                if ($x->comme_no != '') {
                    $sc_desc_txt = $sc_desc_txt . $x->comme_no ;
                }
                if ($x->comme_edt != '') {
                    $sc_desc_txt = $sc_desc_txt.'<br>' . $x->comme_edt .$sc_desc_char ;
                }
                if ($x->sr_desc == '3') {
                    $sr_desc_char = '<br>شارف على الانتهاء';
                } else if ($x->sr_desc == '2') {
                    $sr_desc_char = '<br>منتهي';
                } else if ($x->sr_desc == '1') {
                    $sr_desc_char = '<br>سارية';
                } else {
                    $sr_desc_char = '<br>غير مدخل';
                }


                $sr_desc_txt = '';
                if ($x->rent_no != '') {
                    $sr_desc_txt = $x->rent_no;
                }
                if ($x->rent_edt != '') {
                    $sr_desc_txt = $x->rent_no.'<br>';
                    $sr_desc_txt .=  $x->rent_edt .$sr_desc_char;
                }



$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_respon.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$city_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_mobile.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_location.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sm_desc_txt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sc_desc_txt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sr_desc_txt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sd_desc_txt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>


</tr>';
$i++;
$cn++;
}
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




