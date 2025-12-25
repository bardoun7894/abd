<?php
use Elibyy\TCPDF\Facades\TCPDF;
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf::SetAuthor('Mazayz');
$pdf::SetTitle('Join Report');
$pdf::SetSubject('Join Report details');
$pdf::SetKeywords('Purchase, Sale, Order, Payment');
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::setHeaderFont(Array('andlso', '', 13));
$name="تقرير المخالفات";
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
    $tile_txt='تقرير  المخالفات';
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
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">اسم المحل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">المجموعة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ المخالفة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">قيمة المخالفة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:6%;">حالة دفع</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:6%;">جهة المخالفة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">السبب</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">اسم المسؤول</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">رقم السجل التجاري</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">رقم الرخصة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">بيانات الادخال</td>

</tr>';
    $i=1;
    $cn=0;

            // $list_totl = App\Models\Violation::sumspendcountdesc($violation_month_m,$violation_month_y,$shop_id,$manager_id,$violation_no,$violation_ispay,
            // $comme_no,$municip_no,$shop_respon);
            // $violation_val_all_pay = 0;
            // $violation_val_pay = 0;
            // $violation_val_not_pay = 0;
            // foreach ($list_totl as $x_sum) {
            //     $violation_val_all_pay = $x_sum->violation_val_all_pay;
            //     $violation_val_pay = $x_sum->violation_val_pay;
            //     $violation_val_not_pay = $x_sum->violation_val_not_pay;
            // }


    foreach ($list as $x) {
       /* $calculate_month_val = $x->calculate_month_val;
        $sum_det_calculate_month_pay = $x->sum_det_calculate_month_pay;
        $sum_det_calculate_month_remain = $calculate_month_val - $sum_det_calculate_month_pay;
        if ($sum_det_calculate_month_remain == '0') {
            $calculate_desc = 'مكتمل الدفع';
        } else {
            $calculate_desc = 'متبقي';
        }
        $shop_name = $x->shop_name;
        $calculate_month_desc = $x->calculate_month_desc;
        $calculate_desc = $calculate_desc;
        $count_statement = $x->count_statement;
        $note = $x->note;
        $name = $x->name;
        $created_at = $x->created_at;
        $calculate_month_val_sum=$calculate_month_val_sum+$calculate_month_val;
        $sum_det_calculate_month_pay_sum=$sum_det_calculate_month_pay_sum+$sum_det_calculate_month_pay;
        $sum_det_calculate_month_remain_sum=$sum_det_calculate_month_remain_sum+$sum_det_calculate_month_remain;
*/



        if ($x->violation_ispay == '1') {
                $violation_desc = 'مدفوع';
            } else {
                $violation_desc = 'غير مدفوع';
            }
            $shop_name = $x->shop_name;
            $manager_name = $x->manager_name;
            $violation_val = $x->violation_val;
            $violation_dt=$x->violation_dt;
            $violation_desc =  $violation_desc;
            $violation_side_name =$x->violation_side_name;
            $violation_cause =$x->violation_cause;
            $shop_respon =$x->shop_respon;
            $comme_no =$x->comme_no;
            $municip_no =  $x->municip_no;
            $created_at= $x->name.'-'.$x->created_at;


        $tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$violation_dt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$violation_val.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$violation_desc.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$violation_side_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$violation_cause.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_respon.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$comme_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$municip_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>
</tr>';
        $i++;
        $cn++;

    }
$tbl .= '<tr nobr="true" bordercolor=#666666>
<td colspan="4" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ المطلوب</td>
<td colspan="4"style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي مبلغ المدفوع</td>
<td colspan="4" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ المتبقي</td>
</tr>';
$tbl .= '<tr nobr="true" bordercolor=#666666>


<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$violation_val_all_pay.'</td>
<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$violation_val_pay.'</td>
<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$violation_val_not_pay.'</td>
</tr>';
    $pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}




