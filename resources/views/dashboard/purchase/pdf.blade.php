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
$name="تقرير مصاريف شراء";
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
$tile_txt='تقرير مصاريف شراء';
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
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">رقم الفاتورة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:12%;">تاريخ الفاتورة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">قيمة الفاتورة</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">اسم المورد</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:18%;">قائد المحل</td>
<td nobr="true" style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:8%;">تاريح الادخال</td>
<td nobr="true"  style="text-align:center;border:1px solid #000000;background-color:#e4dcd6;font-weight: bold;width:20%;">الملاحظة</td>

</tr>';
$i=1;
$cn=0;
$purchase_no= "";
$purchase_dt= "";
$purchase_price=0;
$purchase_respon='';
$manager_name='';
$created_at='';
$note='';
$purchase_price_sum=0;

	foreach ($list as $x) {
        $purchase_no = $x->purchase_no;
            $purchase_dt = $x->purchase_dt;
            $purchase_price = $x->purchase_price;
            $purchase_respon = $x->purchase_respon;
            $manager_name= $x->manager_name;
            $created_at = $x->created_at;
            $note = $x->note;
            $purchase_price_sum=$purchase_price_sum+$purchase_price;


$tbl .='<tr bordercolor=#666666 nobr="true">
<td style="text-align:center;border: 1px solid #000000;">'.$i.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$purchase_no.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$purchase_dt.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$purchase_price.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$purchase_respon.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$manager_name.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$created_at.'</td>
<td style="text-align:center;border: 1px solid #000000;">'.$note.'</td>

</tr>';
$i++;
$cn++;

}
$tbl .= '<tr nobr="true" bordercolor=#666666>
<td colspan="4" style="text-align:center;border:1px solid #000000;background-color:#e3f2e4;font-weight:bold;"> الإجمالي المبلغ </td>
<td colspan="4" style="text-align:center;border:1px solid #000000;font-weight:bold;">'.$purchase_price_sum.'</td>

</tr>';

$pdf::writeHTML($tbl_header . $tbl . $tbl_footer, true, false, true, false, 'J');
}
//$pdf::Output('rep_parti.pdf', 'I');




