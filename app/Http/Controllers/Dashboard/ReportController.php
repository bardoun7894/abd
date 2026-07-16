<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workers;
use App\Models\Shop;
use App\Models\Financial;
use App\Models\Calculate;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Violation;


use App\Models\Vacation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use PhpOffice\PhpSpreadsheet\Settings;

use Carbon\Carbon;
use Perm;
use PDF;

class ReportController extends Controller
{












    public function print_purchase_xlsx(Request $request)
    {
        $purchase_id = $request->id;
        $purchase_no = $request->purchase_no;
        $purchase_dt_from = $request->purchase_dt_from;
        $purchase_dt_to = $request->purchase_dt_to;
        $purchase_respon = $request->purchase_respon;
        $manager_id = $request->manager_id;
        $shop_id = $request->shop_id;
        $list = Purchase::serachspenddatarep($purchase_id,$purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id,$shop_id,$request->shops);
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "I1";
        $from = "A2";
        $to = "I2";
        $from_c = "A";
        $to_c = "I";
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير مصاريف شراء");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'رقم الفاتورة');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'تاريخ الفاتورة');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', ' قيمة الفاتورة شامل الضريبة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'اسم المورد');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'المجموعة');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'تاريح الادخال');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'الملاحظة');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'اسم المحل');

        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;

        foreach ($list as $x) {
            $purchase_no = $x->purchase_no;
            $purchase_dt= Carbon::parse($x->purchase_dt)->format('d-m-Y');
            $purchase_price = $x->purchase_price;
            $purchase_respon = $x->purchase_respon;
            $shop = Shop::find($x->shop_id);
            $manager_name= $x->manager_name ?? $shop->manager->manager_name;
            $shop_name= isset($shop) ? ( ($shop->shop_name ." - ". $shop->municip->municip_no) ?? "") : "";
            $created_at = Carbon::parse($x->created_at)->format('d-m-Y');
            $note = $x->note;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $purchase_no);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $purchase_dt);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $purchase_price);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $purchase_respon);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $manager_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $created_at);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $note);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $shop_name);
            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));
    }


    public function print_purchase_pdf(Request $request)
    {
        $purchase_id = $request->id;
        $purchase_no = $request->purchase_no;
        $purchase_dt_from = $request->purchase_dt_from;
        $purchase_dt_to = $request->purchase_dt_to;
        $purchase_respon = $request->purchase_respon;
        $manager_id = $request->manager_id;
        $list = Purchase::serachspenddatarep($purchase_id,$purchase_no, $purchase_dt_from, $purchase_dt_to, $purchase_respon, $manager_id);
        PDF::setHeaderCallback(function ($pdf) {
            //         $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
            //         $comp_name_en = 'NOUR SABAH';
            //         $tax_no_ar = 'برنامج المحوسب';
            //         $tax_no_en = 'Report System';
            //         $comp_det_ar = 'الرقم الضريبي :1111111';
            //         $comp_det_en = 'Tax Number : 1111111';
            //         $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
            //         $pdf->SetFont("aealarabiya", "", 11);
            //         $today = date("Y-m-d");
            //         $html = '<strong>' . $comp_name_ar . '</strong><br/>
            // ' . $comp_det_ar . '<br/>
            // ' . $tax_no_ar . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
            //         $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
            //         $pdf->SetY(30);
            //         $pdf->writeHTML("<hr>", true, false, false, false, '');

            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);



                    $pdf->SetAlpha(0.25);

                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                  //  $pdf->SetMargins(0, 0, 20, 0);

                });


        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.purchase.pdf', $data)->render();
        PDF::Output('purchase.pdf', 'I');
    }

















    public function print_expense_xlsx(Request $request)
    {
        $expense_id = $request->id;
        $expense_type_id = $request->expense_type_id;
        $expense_categoty_id = $request->expense_categoty_id;
        $expense_dt_from = $request->expense_dt_from;
        $expense_dt_to = $request->expense_dt_to;
        $manager_id = $request->manager_id;
        $worker_id = $request->worker_id;
        $shop_id = $request->shop_id;
        $expense_month_desc = $request->expense_month_desc;
        $type = $request->type;
        $det_calculate_month_remain = $request->det_calculate_month_remain;

        $list = Expense::serachspenddataarepll($expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_calculate_month_remain);

        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "N1";
        $from = "A2";
        $to = "N2";
        $from_c = "A";
        $to_c = "N";
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:N1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير مصاريف  تشغيلية ");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'نوع المصروف');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'التصنيف');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'المحل');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'العامل');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'المجموعة');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'الشهر');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'المبلغ');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'المدفوع');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'المتبقي');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'الحالة');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', 'ملاحظة');
        $objPHPExcel->getActiveSheet()->SetCellValue('M2', 'المدخل');
        $objPHPExcel->getActiveSheet()->SetCellValue('N2', 'تاريح الادخال');

        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;

        foreach ($list as $x) {


            if ($x->remain_db == '0') {
                $remain_desc = 'مكتمل الدفع';
            } else {
                $remain_desc = 'متبقي';
            }

            if ( $x->municip_no!='') {
                $municip_no_char = '-' .$x->municip_no ;
            }
            else{
                $municip_no_char='';
            }
            if ( $x->ssn!='') {
                $ssn_char = '-'. $x->ssn;
            }
            else{
                $ssn_char='';
            }
            if ( $x->shop_name!='') {
                $shop_name =  $x->shop_name.'-'.$x->shop_id ;
            }
            else{
                $shop_name='';
            }








           $type_desc= $x->type_desc;
           $expense_type_name= $x->expense_type_name;
            $expense_categoty_name=$x->expense_categoty_name;
          $shop_name=  $shop_name.$municip_no_char;
          $worker_name=  $x->worker_name. $ssn_char;
          $manager_name=  $x->manager_name;
           $expense_month_desc= $x->expense_month_desc;
            $expense_price=$x->expense_price;
            $sum_det_calculate_month_pay=$x->sum_det_calculate_month_pay;
           $remain_db= $x->remain_db;
           // $remain_desc
           $note= $x->note;
           $name= $x->name;
            $created_at = Carbon::parse($x->created_at)->format('d-m-Y');





            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $x->type_desc.'-'.$expense_type_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $expense_categoty_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $shop_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $worker_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $manager_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $expense_month_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $expense_price);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $sum_det_calculate_month_pay);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $remain_db);
            $objPHPExcel->getActiveSheet()->SetCellValue('k' . $rowCount, $remain_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $note);
            $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $name);
            $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, $created_at);

            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));
    }


    public function print_expense_pdf(Request $request)
    {
        $expense_id = $request->id;
        $expense_type_id = $request->expense_type_id;
        $expense_categoty_id = $request->expense_categoty_id;
        $expense_dt_from = $request->expense_dt_from;
        $expense_dt_to = $request->expense_dt_to;
        $manager_id = $request->manager_id;
        $worker_id = $request->worker_id;
        $shop_id = $request->shop_id;
        $expense_month_desc = $request->expense_month_desc;
        $type = $request->type;
        $det_calculate_month_remain = $request->det_calculate_month_remain;
        $list = Expense::serachspenddataall($expense_type_id, $expense_categoty_id, $expense_month_desc, $manager_id, $worker_id, $shop_id,$type,$det_calculate_month_remain);
        PDF::setHeaderCallback(function ($pdf) {
            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);
                    $pdf->SetAlpha(0.25);
                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                });

        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.expense.pdf', $data)->render();
        PDF::Output('expense.pdf', 'I');
    }











    public function print_calculate_xlsx(Request $request)
    {
        $manager_id = $request->manager_id;

        $shop_id = $request->shop_id;
        $calculate_id = $request->id;
        $calculate_month_desc = $request->calculate_month_desc;
        if ($calculate_month_desc != '') {
            $array_name = explode("-", $calculate_month_desc);
            $calculate_month_m = $array_name[0];
            $calculate_month_y = $array_name[1];
        } else {
            $calculate_month_m = '';
            $calculate_month_y = '';
        }
        $list = Calculate::serachspenddatarep($calculate_id,$calculate_month_m, $calculate_month_y, $shop_id,$manager_id);


        //  $list = vacation::serachspendrep('','','','','');

        //  $spreadsheet = new Spreadsheet();
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "L1";
        $from = "A2";
        $to = "L2";
        $from_c = "A";
        $to_c = "L";

        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير  حسابات المحل");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم المحل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'شهر الدفع');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'حالة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'المبلغ المطلوب');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'اجمالي المدفوع');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'اجمالي المتبقي');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'عدد الاقساط');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'الملاحظة');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'المدخل');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'تاريخ الادخال');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', '');
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;

        foreach ($list as $x) {
            $calculate_month_val = $x->calculate_month_val;
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
            $created_at = Carbon::parse($x->created_at)->format('d-m-Y');


            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $shop_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $calculate_month_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $calculate_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $calculate_month_val);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $sum_det_calculate_month_pay);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $sum_det_calculate_month_remain);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $count_statement);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $note);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $name);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $created_at);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, '');

            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));


    }


    public function print_calculate_pdf(Request $request)
    {
        $manager_id = $request->manager_id;

        $shop_id = $request->shop_id;
        $calculate_id = $request->id;
        $calculate_month_desc = $request->calculate_month_desc;
        if ($calculate_month_desc != '') {
            $array_name = explode("-", $calculate_month_desc);
            $calculate_month_m = $array_name[0];
            $calculate_month_y = $array_name[1];
        } else {
            $calculate_month_m = '';
            $calculate_month_y = '';
        }
        $list = Calculate::serachspenddatarep($calculate_id,$calculate_month_m, $calculate_month_y, $shop_id,$manager_id);

        PDF::setHeaderCallback(function ($pdf) {
            //         $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
            //         $comp_name_en = 'NOUR SABAH';
            //         $tax_no_ar = 'برنامج المحوسب';
            //         $tax_no_en = 'Report System';
            //         $comp_det_ar = 'الرقم الضريبي :1111111';
            //         $comp_det_en = 'Tax Number : 1111111';
            //         $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
            //         $pdf->SetFont("aealarabiya", "", 11);
            //         $today = date("Y-m-d");
            //         $html = '<strong>' . $comp_name_ar . '</strong><br/>
            // ' . $comp_det_ar . '<br/>
            // ' . $tax_no_ar . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
            //         $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
            //         $pdf->SetY(30);
            //         $pdf->writeHTML("<hr>", true, false, false, false, '');

            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);



                    $pdf->SetAlpha(0.25);

                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                  //  $pdf->SetMargins(0, 0, 20, 0);

                });

        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.calculate.pdf', $data)->render();
        PDF::Output('calculate.pdf', 'I');
    }




    public function print_fnancial_xlsx(Request $request)
    {
        $worker_id = $request->worker_id;
        $manager_id = $request->manager_id;

        $financial_id = $request->id;
        $financial_month_desc = $request->financial_month_desc;
        if ($financial_month_desc != '') {
            $array_name = explode("-", $financial_month_desc);
            $financial_month_m = $array_name[0];
            $financial_month_y = $array_name[1];
        } else {
            $financial_month_m = '';
            $financial_month_y = '';
        }
        $list = Financial::serachspenddatarep($financial_id,$financial_month_m, $financial_month_y, $worker_id,$manager_id);
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "L1";
        $from = "A2";
        $to = "L2";
        $from_c = "A";
        $to_c = "L";
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير  حسابات العمال");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم العامل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'شهر الدفع');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'حالة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'المبلغ المطلوب');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'اجمالي المدفوع');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'اجمالي المتبقي');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'عدد الاقساط');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'الملاحظة');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'المدخل');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'تاريخ الادخال');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', '');
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;

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
            $created_at = Carbon::parse($x->created_at)->format('d-m-Y');


            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $worker_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $financial_month_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $financial_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $financial_month_val);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $sum_det_financial_month_pay);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $sum_det_financial_month_remain);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $count_statement);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $note);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $name);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $created_at);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, '');

            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));
    }


    public function print_fnancial_pdf(Request $request)
    {
        $manager_id = $request->manager_id;
        $worker_id = $request->worker_id;
        $financial_id = $request->id;
        $financial_month_desc = $request->financial_month_desc;
        if ($financial_month_desc != '') {
            $array_name = explode("-", $financial_month_desc);
            $financial_month_m = $array_name[0];
            $financial_month_y = $array_name[1];
        } else {
            $financial_month_m = '';
            $financial_month_y = '';
        }
        $list = Financial::serachspenddatarep($financial_id,$financial_month_m, $financial_month_y, $worker_id,$manager_id);
        PDF::setHeaderCallback(function ($pdf) {
            //         $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
            //         $comp_name_en = 'NOUR SABAH';
            //         $tax_no_ar = 'برنامج المحوسب';
            //         $tax_no_en = 'Report System';
            //         $comp_det_ar = 'الرقم الضريبي :1111111';
            //         $comp_det_en = 'Tax Number : 1111111';
            //         $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
            //         $pdf->SetFont("aealarabiya", "", 11);
            //         $today = date("Y-m-d");
            //         $html = '<strong>' . $comp_name_ar . '</strong><br/>
            // ' . $comp_det_ar . '<br/>
            // ' . $tax_no_ar . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
            //         $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
            //         $pdf->SetY(30);
            //         $pdf->writeHTML("<hr>", true, false, false, false, '');

            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);



                    $pdf->SetAlpha(0.25);

                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                  //  $pdf->SetMargins(0, 0, 20, 0);

                });

                PDF::setFooterCallback(function ($pdf) {
                    $footertext = "تمت طباعة التقرير بواسطة الموظف  :  "
                        . Auth::user()->name . '                    '
                        . "التاريخ : " . date("Y-m-d") . '                     '
                        . "الوقت : " . date("h:i:sa") . '                     '
                        . "ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع " . '                     ';
                    $pdf->SetY(-15);
                    $pdf->SetFont('almohanad', 'B', 9);
                    $pdf->Cell(0, 10, $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');

                });

        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.financial.pdf', $data)->render();
        PDF::Output('financial.pdf', 'I');
    }





    public function print_shop_xlsx(Request $request)
    {

        $order_date = $request->order_date;
        $comme_month = $request->comme_month;
        $comme_year = $request->comme_year;
        $municip_month = $request->municip_month;
        $municip_year = $request->municip_year;
        $rentpay_month = $request->rentpay_month;
        $rentpay_year = $request->rentpay_year;

        $shop_id = $request->id;
        $shop_name = $request->shop_name;
        $shop_mobile = $request->shop_mobile;
        $manager_id = $request->manager_id;
        $city_id = $request->city_id;
        $comme_no = $request->comme_no;
        $municip_no = $request->municip_no;
        $rentpay_price = $request->rentpay_price;
        $list = Shop::serachspenddata($shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no,$rentpay_price,$order_date,$comme_month,$comme_year,$municip_month,$municip_year,$rentpay_month,$rentpay_year);
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "L1";
        $from = "A2";
        $to = "L2";
        $from_c = "A";
        $to_c = "L";

        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير  المحلات");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم المحل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'المجموعة');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'اسم المسؤول');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'المدينة');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'رقم جوال المسؤول');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'موقع المحل');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'معلومات البلدية');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'معلومات السجل التجاري');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'معلومات الإيجار');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'معلومات الدفاع المدني');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', 'تاريخ الادخال');
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;
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
                    $sm_desc_char = '-شارف على الانتهاء';
                } else if ($x->sm_desc == '2') {
                    $sm_desc_char = '-منتهي';
                } else if ($x->sm_desc == '1') {
                    $sm_desc_char = '-سارية';
                } else {
                    $sm_desc_char = '-غير مدخل';
                }
                $sm_desc_txt = '';



                if ($x->municip_no != '') {
                    $sm_desc_txt = '' . $x->municip_no . '';
                }
                if ($x->municip_edt != '') {
                    $sm_desc_txt = '' . $x->municip_no . '-';
                    $sm_desc_txt .=  $x->municip_edt  . $sm_desc_char ;
                }


                if ($x->shel_desc == '3') {
                    $shel_desc_char = '-شارف على الانتهاء';
                } else if ($x->shel_desc == '2') {
                    $shel_desc_char = '-منتهي';
                } else if ($x->shel_desc == '1') {
                    $shel_desc_char = '-سارية';
                } else {
                    $shel_desc_char = '-غير مدخل';
                }
                $shel_desc_txt = '';



                if ($x->health_no != '') {
                    $shel_desc_txt = '' . $x->health_no . '';
                }
                if ($x->health_edt != '') {
                    $shel_desc_txt .= '' . $x->health_no . '-';
                    $shel_desc_txt .= '' . $x->health_edt . '' . $shel_desc_char . '';
                }

                if ($x->sd_desc == '3') {
                    $sd_desc_char = '-شارف على الانتهاء';
                } else if ($x->sd_desc == '2') {
                    $sd_desc_char = '-منتهي';
                } else if ($x->sd_desc == '1') {
                    $sd_desc_char = '-سارية';
                } else {
                    $sd_desc_char = '-غير مدخل';
                }
                $sd_desc_txt = '';


                if ($x->defence_no != '') {
                    $sd_desc_txt = '' . $x->defence_no ;
                }
                if ($x->defence_edt != '') {
                    $sd_desc_txt = '' . $x->defence_no.'-' ;

                    $sd_desc_txt .=  $x->defence_edt .$sd_desc_char ;
                }
                if ($x->sc_desc == '3') {
                    $sc_desc_char = '-شارف على الانتهاء';
                } else if ($x->sc_desc == '2') {
                    $sc_desc_char = '-منتهي';
                } else if ($x->sc_desc == '1') {
                    $sc_desc_char = '-سارية';
                } else {
                    $sc_desc_char = '-غير مدخل';
                }
                $sc_desc_txt = '';


                if ($x->comme_no != '') {
                    $sc_desc_txt = $sc_desc_txt . $x->comme_no ;
                }
                if ($x->comme_edt != '') {
                    $sc_desc_txt = $sc_desc_txt.'-' . $x->comme_edt .$sc_desc_char ;
                }
                if ($x->sr_desc == '3') {
                    $sr_desc_char = '-شارف على الانتهاء';
                } else if ($x->sr_desc == '2') {
                    $sr_desc_char = '-منتهي';
                } else if ($x->sr_desc == '1') {
                    $sr_desc_char = '-سارية';
                } else {
                    $sr_desc_char = '-غير مدخل';
                }


                $sr_desc_txt = '';
                if ($x->rent_no != '') {
                    $sr_desc_txt = $x->rent_no;
                }
                if ($x->rent_edt != '') {
                    $sr_desc_txt = $x->rent_no.'-';
                    $sr_desc_txt .=  $x->rent_edt .$sr_desc_char;
                }





            $created_at= Carbon::parse($x->created_at)->format('d-m-Y');
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $shop_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $manager_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $shop_respon);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $city_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $shop_mobile);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $shop_location);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $sm_desc_txt);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $sc_desc_txt);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $sr_desc_txt);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $sd_desc_txt);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $created_at);
            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));


    }


    public function print_shop_pdf(Request $request)
    {
        $shop_id = $request->id;
        $shop_name = $request->shop_name;
        $shop_mobile = $request->shop_mobile;
        $manager_id = $request->manager_id;
        $city_id = $request->city_id;
        $comme_no = $request->comme_no;
        $municip_no = $request->municip_no;
        $rentpay_price = $request->rentpay_price;
        PDF::setHeaderCallback(function ($pdf) {

            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);



                    $pdf->SetAlpha(0.25);

                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                  //  $pdf->SetMargins(0, 0, 20, 0);

                });

                PDF::setFooterCallback(function ($pdf) {
                    $footertext = "تمت طباعة التقرير بواسطة الموظف  :  "
                        . Auth::user()->name . '                    '
                        . "التاريخ : " . date("Y-m-d") . '                     '
                        . "الوقت : " . date("h:i:sa") . '                     '
                        . "ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع " . '                     ';
                    $pdf->SetY(-15);
                    $pdf->SetFont('almohanad', 'B', 9);
                    $pdf->Cell(0, 10, $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');

                });

                $order_date = $request->order_date;
                $comme_month = $request->comme_month;
                $comme_year = $request->comme_year;
                $municip_month = $request->municip_month;
                $municip_year = $request->municip_year;
                $rentpay_month = $request->rentpay_month;
                $rentpay_year = $request->rentpay_year;

                $list = Shop::serachspenddata($shop_name, $shop_mobile, $manager_id, $city_id, $comme_no, $municip_no,$rentpay_price,$order_date,$comme_month,$comme_year,$municip_month,$municip_year,$rentpay_month,$rentpay_year);
                $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.shop.pdf', $data)->render();
        PDF::Output('shop.pdf', 'I');
    }





















    public function print_violation_xlsx(Request $request)
    {
        $violation_id = $request->id;
        $shop_id = $request->shop_id;
        $manager_id = $request->manager_id;
        $violation_month_desc = $request->violation_month_desc;
        if ($violation_month_desc != '') {
            $array_name = explode("-", $violation_month_desc);
            $violation_month_m = $array_name[0];
            $violation_month_y = $array_name[1];
        } else {
            $violation_month_m = '';
            $violation_month_y = '';
        }

        $violation_no = $request->violation_no;
        $violation_ispay = $request->violation_ispay;
        $comme_no = $request->comme_no;
        $municip_no = $request->municip_no;
        $shop_respon = $request->shop_respon;

    $list = Violation::serachspenddatarep($violation_id,$violation_month_m,$violation_month_y,$shop_id,$manager_id,$violation_no,$violation_ispay,
        $comme_no,$municip_no,$shop_respon);

        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "L1";
        $from = "A2";
        $to = "L2";
        $from_c = "A";
        $to_c = "L";
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:L1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير  المخالفات");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم المحل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'المجموعة');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'تاريخ المخالفة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'قيمة المخالفة');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'حالة دفع');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'جهة المخالفة');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'السبب');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'اسم المسؤول');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'رقم السجل التجاري');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'رقم الرخصة');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', 'بيانات الادخال');
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;
        foreach ($list as $x) {

            if ($x->violation_ispay == '1') {
                $violation_desc = 'مدفوع';
            } else {
                $violation_desc = 'غير مدفوع';
            }
            $shop_name = $x->shop_name;
            $manager_name = $x->manager_name;
            $violation_val = $x->violation_val;
            $violation_dt= Carbon::parse($x->violation_dt)->format('d-m-Y');
            $violation_desc =  $violation_desc;
            $violation_side_name =$x->violation_side_name;
            $violation_cause =$x->violation_cause;
            $shop_respon =$x->shop_respon;
            $comme_no =$x->comme_no;
            $municip_no =  $x->municip_no;
            $created_at= $x->name.'-'.Carbon::parse($x->created_at)->format('d-m-Y');


            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $shop_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $manager_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $violation_dt);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $violation_val);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $violation_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $violation_side_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $violation_cause);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $shop_respon);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $comme_no);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $municip_no);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $created_at);
            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));


    }


    public function print_violation_pdf(Request $request)
    {
        $violation_id = $request->id;
        $shop_id = $request->shop_id;
        $manager_id = $request->manager_id;
        $violation_month_desc = $request->violation_month_desc;
        if ($violation_month_desc != '') {
            $array_name = explode("-", $violation_month_desc);
            $violation_month_m = $array_name[0];
            $violation_month_y = $array_name[1];
        } else {
            $violation_month_m = '';
            $violation_month_y = '';
        }

        $violation_no = $request->violation_no;
        $violation_ispay = $request->violation_ispay;
        $comme_no = $request->comme_no;
        $municip_no = $request->municip_no;
        $shop_respon = $request->shop_respon;

        PDF::setHeaderCallback(function ($pdf) {
            //         $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
            //         $comp_name_en = 'NOUR SABAH';
            //         $tax_no_ar = 'برنامج المحوسب';
            //         $tax_no_en = 'Report System';
            //         $comp_det_ar = 'الرقم الضريبي :1111111';
            //         $comp_det_en = 'Tax Number : 1111111';
            //         $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
            //         $pdf->SetFont("aealarabiya", "", 11);
            //         $today = date("Y-m-d");
            //         $html = '<strong>' . $comp_name_ar . '</strong><br/>
            // ' . $comp_det_ar . '<br/>
            // ' . $tax_no_ar . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
            //         $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
            //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
            //         $pdf->SetY(30);
            //         $pdf->writeHTML("<hr>", true, false, false, false, '');

            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetY(30);



                    $pdf->SetAlpha(0.25);

                    $pdf->SetY(16);
                    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
                  //  $pdf->SetMargins(0, 0, 20, 0);

                });

                PDF::setFooterCallback(function ($pdf) {
                    $footertext = "تمت طباعة التقرير بواسطة الموظف  :  "
                        . Auth::user()->name . '                    '
                        . "التاريخ : " . date("Y-m-d") . '                     '
                        . "الوقت : " . date("h:i:sa") . '                     '
                        . "ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع " . '                     ';
                    $pdf->SetY(-15);
                    $pdf->SetFont('almohanad', 'B', 9);
                    $pdf->Cell(0, 10, $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');

                });


        $list = Violation::serachspenddatarep($violation_id,$violation_month_m,$violation_month_y,$shop_id,$manager_id,$violation_no,$violation_ispay,
        $comme_no,$municip_no,$shop_respon);


        $list_totl = Violation::sumspendcountdesc($violation_id,$violation_month_m,$violation_month_y,$shop_id,$manager_id,$violation_no,$violation_ispay,
        $comme_no,$municip_no,$shop_respon);
        $violation_val_all_pay = 0;
        $violation_val_pay = 0;
        $violation_val_not_pay = 0;
        foreach ($list_totl as $x_sum) {
            $violation_val_all_pay = $x_sum->violation_val_all_pay;
            $violation_val_pay = $x_sum->violation_val_pay;
            $violation_val_not_pay = $x_sum->violation_val_not_pay;
        }

        $data["violation_val_all_pay"] = $violation_val_all_pay;
        $data["violation_val_pay"] = $violation_val_pay;
        $data["violation_val_not_pay"] = $violation_val_not_pay;



        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.violation.pdf', $data)->render();
        PDF::Output('violation.pdf', 'I');
    }






















































    public function print_worker_xlsx(Request $request)
    {
        $worker_id = $request->id;
        $worker_name = $request->worker_name;
        $ssn = $request->ssn;
        $work_place_id = $request->work_place_id;
        $doe = $request->doe;
        $updatedcancal_at = $request->updatedcancal_at;
        $job_id = $request->job_id;
        $end_dt = $request->end_dt;
        $end_p_dt = $request->end_p_dt;
        $manager_id= $request->manager_id;
        $inside= $request->inside;
        $is_imp= $request->is_imp;
        $nation= $request->nation;
        $order_date = $request["order_date"];


        //  $list = vacation::serachspendrep('','','','','');

        // $list = Workers::workreport($worker_id, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation);
        $list = Workers::serachspenddata($worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt, $manager_id, $inside, $is_imp, $nation , $order_date , $request["residence_month"],$request["residence_year"],$request["passport_month"],$request["passport_year"]);


        //  $spreadsheet = new Spreadsheet();
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "M1";
        $from = "A2";
        $to = "M2";
        $from_c = "A";
        $to_c = "M";

        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:M1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير  العمال");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم العامل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'رقم الإقامة / الوطني للسعوديين');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'المجموعة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'تاريخ اصدار الاقامة');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'تاريخ إنتهاء الإقامة');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'تاريخ انتهاء الجواز');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'الجنسية');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'تاريخ التعيين');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'مكان العمل');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'المهنة');
        $objPHPExcel->getActiveSheet()->SetCellValue('L2', 'التواجد');
        $objPHPExcel->getActiveSheet()->SetCellValue('M2', 'تاريخ الادخال');

        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;

        foreach ($list as $x) {
            $worker_name = $x->worker_name;
            $ssn = $x->ssn;

            $work_place_id = $x->work_place_id;
            $work_place_name = $x->work_place_name;
            $manager_name = $x->manager_name;

            $dos = $x->dos;
            $doe = $x->doe;
            $dop = $x->dop;
            $nation_name_ar = $x->nation_name_ar;
            $dow = $x->dow;
            $job_name = $x->job_name;
            $created_at = $x->created_at;

            if ($x->inside == 1) {
                $inside_desc = 'داخل المملكة';
            } else {
                $inside_desc = 'خارج المملكة';
            }


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


            if ($x->inside == 1) {
                $inside_desc = 'داخل المملكة';
            } else {
                $inside_desc = 'خارج المملكة';
            }

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $worker_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $ssn);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $manager_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $dos);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $doe.'-'.$doe_desc_char);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $dop.'-'.$dop_desc_char);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $nation_name_ar);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $dow);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $work_place_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $job_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $inside_desc);
            $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $created_at);

            $i++;
            $rowCount++;
        }
        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));


    }


    public function print_worker_pdf(Request $request)
    {
        $worker_id = $request->id;
        $worker_name = $request->worker_name;
        $ssn = $request->ssn;
        $work_place_id = $request->work_place_id;
        $doe = $request->doe;
        $updatedcancal_at = $request->updatedcancal_at;
        $job_id = $request->job_id;
        $end_dt = $request->end_dt;
        $end_p_dt = $request->end_p_dt;
        $manager_id= $request->manager_id;
        $inside= $request->inside;
        $is_imp= $request->is_imp;
        $nation= $request->nation;

        PDF::setHeaderCallback(function ($pdf) {
    //         $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
    //         $comp_name_en = 'NOUR SABAH';
    //         $tax_no_ar = 'برنامج المحوسب';
    //         $tax_no_en = 'Report System';
    //         $comp_det_ar = 'الرقم الضريبي :1111111';
    //         $comp_det_en = 'Tax Number : 1111111';
    //         $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
    //         $pdf->SetFont("aealarabiya", "", 11);
    //         $today = date("Y-m-d");
    //         $html = '<strong>' . $comp_name_ar . '</strong><br/>
    // ' . $comp_det_ar . '<br/>
    // ' . $tax_no_ar . '<br/>';
    //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
    //         $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
    //         $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
    //         $pdf->SetY(30);
    //         $pdf->writeHTML("<hr>", true, false, false, false, '');

    $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'Logopdf.jpg'), 40, 5, 30, '', 'jpeg', '', 'L', false, 10, '', false, false, 0, false, false, false);
    $pdf->SetY(30);



            $pdf->SetAlpha(0.25);

            $pdf->SetY(16);
            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . 'logo.jpg'), 170, 90, 0, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
          //  $pdf->SetMargins(0, 0, 20, 0);

        });

        PDF::setFooterCallback(function ($pdf) {
            $footertext = "تمت طباعة التقرير بواسطة الموظف  :  "
                . Auth::user()->name . '                    '
                . "التاريخ : " . date("Y-m-d") . '                     '
                . "الوقت : " . date("h:i:sa") . '                     '
                . "ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع " . '                     ';
            $pdf->SetY(-15);
            $pdf->SetFont('almohanad', 'B', 9);
            $pdf->Cell(0, 10, $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });


        $list = Workers::workreport($worker_id, $worker_name, $ssn, $work_place_id, $doe, $updatedcancal_at, $job_id, $end_dt, $end_p_dt,$manager_id,$inside,$is_imp,$nation);
        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.workers.pdf', $data)->render();
        PDF::Output('Workers.pdf', 'I');
    }


    public function print_vacation_pdf(Request $request)
    {
        $vacation_id = $request->id;
        $worker_id = $request->worker_id;
        $vacation_type_id = $request->vacation_type_id;
        $vacation_month_desc = $request->vacation_month_desc;
        if ($vacation_month_desc != '') {
            $array_name = explode("-", $vacation_month_desc);
            $vacation_month_m = $array_name[0];
            $vacation_month_y = $array_name[1];
        } else {
            $vacation_month_m = '';
            $vacation_month_y = '';
        }

        PDF::setHeaderCallback(function ($pdf) {
            $comp_name_ar = 'شركة عبدالله سعيد ال هنيدي للمقاولات  ';
            $comp_name_en = 'NOUR SABAH';
            $tax_no_ar = 'برنامج المحوسب';
            $tax_no_en = 'Report System';
            $comp_det_ar = 'الرقم الضريبي :1111111';
            $comp_det_en = 'Tax Number : 1111111';
//   $pdf->Image('@'.file_get_contents( K_PATH_IMAGES.'LOQO2018.png'), 125, 5, 25, '', 'PNG', '', '}', false, 10, '', false, false, 0, false, false, false);
//  $pdf->Image('@'.file_get_contents( K_PATH_IMAGES.'LOGO.png'), 125, 5, 25, '', 'PNG', '', 'C', false, 10, '', false, false, 0, false, false, false);
            $pdf->Image('@' . file_get_contents(K_PATH_IMAGES . '11_logo_sjpg.jpg'), 150, 5, 17, '', 'jpeg', '', 'C', false, 10, '', false, false, 0, false, false, false);
            $pdf->SetFont("aealarabiya", "", 11);
            $today = date("Y-m-d");
            $html = '<strong>' . $comp_name_ar . '</strong><br/>
    ' . $comp_det_ar . '<br/>
    ' . $tax_no_ar . '<br/>';
            $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'R');
            $html = '<strong>' . $comp_name_en . '</strong><br/>' . $comp_det_en . '<br/>' . $tax_no_en . '<br/>';
            $pdf->writeHTMLCell($w = 0, $h = 10, $x = 10, $y = 8, $html, $border = 0, $ln = 0, $fill = false, $reseth = true, $align = 'L');
            $pdf->SetY(30);
            $pdf->writeHTML("<hr>", true, false, false, false, '');
        });

        PDF::setFooterCallback(function ($pdf) {
            $footertext = "تمت طباعة التقرير بواسطة الموظف  :  "
                . Auth::user()->name . '                    '
                . "التاريخ : " . date("Y-m-d") . '                     '
                . "الوقت : " . date("h:i:sa") . '                     '
                . "ملاحظة:هذا التقرير معتمد من صاحب الصلاحية و لا يحتاج لتوقيع " . '                     ';
            $pdf->SetY(-15);
            $pdf->SetFont('almohanad', 'B', 9);
            $pdf->Cell(0, 10, $footertext, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });


        $list = vacation::serachspendrep($vacation_id, $vacation_month_m, $vacation_month_y, $worker_id, $vacation_type_id);
        $data["list"] = $list;
        $data["ADDED_INFO_NO"] = '413346578';
        $html = view('dashboard.vacation.pdf', $data)->render();
        PDF::Output('Workers.pdf', 'I');
    }


    public function print_vacation_xlsx(Request $request)
    {
        $vacation_id = $request->id;
        $worker_id = $request->worker_id;
        $vacation_type_id = $request->vacation_type_id;
        $vacation_month_desc = $request->vacation_month_desc;
        if ($vacation_month_desc != '') {
            $array_name = explode("-", $vacation_month_desc);
            $vacation_month_m = $array_name[0];
            $vacation_month_y = $array_name[1];
        } else {
            $vacation_month_m = '';
            $vacation_month_y = '';
        }

        $list = vacation::serachspendrep($vacation_id, $vacation_month_m, $vacation_month_y, $worker_id, $vacation_type_id);
        $objPHPExcel = new Spreadsheet();
        $getActiveSheet = $objPHPExcel->getActiveSheet();
        $from_h = "A1";
        $to_h = "K1";
        $from = "A2";
        $to = "K2";
        $from_c = "A";
        $to_c = "K";

        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $objPHPExcel->getActiveSheet()->setTitle('NourSabah');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("NourSabah");
        $objPHPExcel->getProperties()->setLastModifiedBy("NourSabah");
        $objPHPExcel->getProperties()->setTitle("NourSabah");
        $objPHPExcel->getProperties()->setSubject("NourSabah");
        $objPHPExcel->getProperties()->setDescription("NourSabah");
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("تقرير الاجازات");
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('33F0FF'); //FF3399 33F0FF F28A8C
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle("$from_h:$to_h")->getFont()->setItalic(false);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(75);
        $objPHPExcel->getTheme()->setThemeFontName('custom')->setMinorFontValues('Calibri', 'Arial', 'Arial', []);
        $objPHPExcel->getDefaultStyle()->getFont()->setScheme('minor');
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A2', '#');
        $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'اسم العامل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C2', 'بداية الاجازة');
        $objPHPExcel->getActiveSheet()->SetCellValue('D2', 'نهاية الاجازة');
        $objPHPExcel->getActiveSheet()->SetCellValue('E2', 'عدد ايام الاجازة');
        $objPHPExcel->getActiveSheet()->SetCellValue('F2', 'نوع الاجازة');
        $objPHPExcel->getActiveSheet()->SetCellValue('G2', 'المسمى الوظيفي');
        $objPHPExcel->getActiveSheet()->SetCellValue('H2', 'مكان العمل');
        $objPHPExcel->getActiveSheet()->SetCellValue('I2', 'ملاحظات  ');
        $objPHPExcel->getActiveSheet()->SetCellValue('J2', 'مدخل البيانات');
        $objPHPExcel->getActiveSheet()->SetCellValue('K2', 'تاريخ الادخال');
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->getFont()->setItalic(false);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle("$from:$to")->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'D3D3D3'))));
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
            $sheet = $objPHPExcel->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
        $rowCount = 3;
        $objPHPExcel->getActiveSheet()->setRightToLeft(true);
        $i = 1;
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
            $created_at = Carbon::parse($x->created_at)->format('d-m-Y');;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $i);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $worker_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $start);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $end);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $count_day);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $vacation_type_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $job_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $work_place_name);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $note);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $name);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $created_at);

            $i++;
            $rowCount++;
        }


        $objWriter = new Xlsx($objPHPExcel);
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="myfile.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = array(
            'op' => 'ok',
            'file' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        die(json_encode($response));


    }


    public function index()
    {
        // NOTE: previously returned view('dashboard.workers.index') by mistake — this
        // route (dashboard.report.index / GET /dashboard/report) has no menu link and no
        // other view/test depends on it, so pointing it at the real reports page is safe.
        $page_title = 'اسأل بياناتك — تقارير الذكاء الاصطناعي';
        return view('dashboard.report.index', compact('page_title'));
    }

    /**
     * Spec 005 T-B2 — NL period summary. Computes aggregates in PHP (fixed whitelist,
     * no raw rows sent to Gemini) then asks Gemini to phrase an Arabic narrative.
     */
    public function aiNarrate(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $service = app(\App\Services\ReportsNlService::class);
        $aggregates = $service->periodAggregates($request->date_from, $request->date_to);

        try {
            $result = $service->narrate($aggregates);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => 'تعذّر توليد الملخص: '.$e->getMessage()], 422);
        }

        \App\Services\AuditLogger::log('report', null, \App\Services\AuditLogger::EXTRACT, [
            'note' => 'ملخص تقارير بالذكاء الاصطناعي (اسأل بياناتك)',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'summary' => $result['summary'],
                'aggregates' => $aggregates,
            ],
        ]);
    }

    /**
     * Spec 005 T-B2 — SAFE ask-your-data. The model NEVER generates SQL and NEVER sees
     * raw rows; it only phrases an Arabic answer around the fixed whitelist of PHP-computed
     * aggregates (ReportsNlService::ALLOWED_METRICS).
     */
    public function aiAsk(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $service = app(\App\Services\ReportsNlService::class);
        $aggregates = $service->periodAggregates($request->date_from, $request->date_to);

        try {
            $result = $service->answer($request->question, $aggregates);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message_out' => 'تعذّر توليد الإجابة: '.$e->getMessage()], 422);
        }

        \App\Services\AuditLogger::log('report', null, \App\Services\AuditLogger::EXTRACT, [
            'note' => 'سؤال بياناتك بالذكاء الاصطناعي: '.$request->question,
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'answer' => $result['answer'],
                'metrics_used' => $result['metrics_used'],
            ],
        ]);
    }

}
