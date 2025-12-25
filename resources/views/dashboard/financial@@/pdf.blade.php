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
$name="تقرير مصاريف العمال";
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
$tile_txt='تقرير مصاريف العمال';
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
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">اسم العامل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">شهر الدفع</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">حالة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المبلغ المطلوب</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">اجمالي المدفوع</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">اجمالي المتبقي</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">عدد الاقساط</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">الملاحظة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">المدخل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريخ الادخال</td>

</tr>';
$i=1;
$cn=0;
$work_place_id= "";
$work_place_name= "";
$financial_month_val_sum=0;
$sum_det_financial_month_pay_sum=0;
$sum_det_financial_month_remain_sum=0;


	foreach ($list as $x) {
        $financial_month_val = $x->financial_month_val;
            $sum_det_financial_month_pay = $x->sum_det_financial_month_pay;
            $sum_det_financial_month_remain = $financial_month_val - $sum_det_financial_month_pay;
            if ($sum_det_financial_month_remain == '0') {
                $financial_desc = 'مكتمل الدفع';
            } else {
                $financial_desc = 'متبقي';
            }
            $worker_name = $x->worker_name;
            $financial_month_desc = $x->financial_month_desc;
            $financial_desc = $financial_desc;
            $count_statement = $x->count_statement;
            $note = $x->note;
            $name = $x->name;
            $created_at = $x->created_at;
            $financial_month_val_sum=$financial_month_val_sum+$financial_month_val;
            $sum_det_financial_month_pay_sum=$sum_det_financial_month_pay_sum+$sum_det_financial_month_pay;
$sum_det_financial_month_remain_sum=$sum_det_financial_month_remain_sum+$sum_det_financial_month_remain;




$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$worker_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$financial_month_desc.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$financial_desc.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$financial_month_val.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sum_det_financial_month_pay.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$sum_det_financial_month_remain.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$count_statement.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$note.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>

</tr>';
$i++;
$cn++;

}
$tbl .= '<tr nobr="true" bordercolor=#666666>
<td colspan="4" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ المطلوب</td>
<td colspan="4"style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي مبلغ المدفوع</td>
<td colspan="3" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ المتبقي</td>
</tr>';
$tbl .= '<tr nobr="true" bordercolor=#666666>
<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$financial_month_val_sum.'</td>
<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$sum_det_financial_month_pay_sum.'</td>
<td colspan="3" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$sum_det_financial_month_remain_sum.'</td>
</tr>';
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




