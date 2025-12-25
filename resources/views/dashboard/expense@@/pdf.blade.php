<?php
use Elibyy\TCPDF\Facades\TCPDF;
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf::SetAuthor('Mazayz');
$pdf::SetTitle('Join Report');
$pdf::SetSubject('Join Report details');
$pdf::SetKeywords('expense, Sale, Order, Payment');
$pdf::SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf::setHeaderFont(Array('andlso', '', 13));
$name="تقرير مصاريف  تشغيلية ";
$pdf::SetTitle($name);
$pdf::SetMargins(5,30,5);
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

$pdf::SetFont("xnahid", "B", 12);
$tile_txt='تقرير مصاريف  تشغيلية ';
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
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">نوع المصروف</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">التصنيف</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">تفصيل الصرف</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">المبلغ</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">قائد المحل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">المحل</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">العامل</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">الملاحظة</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:10%;">المدخل</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:7%;">تاريح الادخال</td>

</tr>';
$i=1;
$cn=0;
$expense_no= "";
$expense_dt= "";
$expense_price=0;
$expense_respon='';
$manager_name='';
$created_at='';
$note='';
$expense_price_sum=0;

	foreach ($list as $x) {
            $expense_type_name = $x->expense_type_name;
            $expense_categoty_name = $x->expense_categoty_name;
            $expense_respon = $x->expense_respon;
            $expense_price = $x->expense_price;
            $manager_name = $x->manager_name;
            $shop_name = $x->shop_name;
            $worker_name = $x->worker_name;
            $note = $x->note;
            $name = $x->name;
            $created_at =$x->created_at;
            $expense_price_sum=$expense_price_sum+$expense_price;
$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$expense_type_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$expense_categoty_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$expense_respon.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$expense_price.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$shop_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$worker_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$note.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>

</tr>';
$i++;
$cn++;

}
$tbl .= '<tr nobr="true" bordercolor=#666666>
<td colspan="6" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ </td>
<td colspan="5" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$expense_price_sum.'</td>
</tr>';
$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




