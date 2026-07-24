<?php

use App\Services\ExcelReportStyler;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Fill;

test('totalsRow writes the label and each money-column total with the money number format', function () {
    $ss = ExcelReportStyler::newBook('اختبار الإجمالي');
    $sheet = $ss->getActiveSheet();

    ExcelReportStyler::titleRow($sheet, 'اختبار', 'D');
    $lastCol = ExcelReportStyler::headerRow($sheet, ['#', 'الاسم', 'مبلغ 1', 'مبلغ 2']);

    $sheet->setCellValue('A3', 1);
    $sheet->setCellValue('B3', 'صف 1');
    $sheet->setCellValue('C3', 100.5);
    $sheet->setCellValue('D3', 50.25);

    $sheet->setCellValue('A4', 2);
    $sheet->setCellValue('B4', 'صف 2');
    $sheet->setCellValue('C4', 200.0);
    $sheet->setCellValue('D4', 30.75);

    ExcelReportStyler::finalize($sheet, $lastCol, 3, 4, ['C', 'D']);

    // Grand-totals footer row, right below the last data row.
    ExcelReportStyler::totalsRow($sheet, 5, 'A', 'الإجمالي', ['C' => 300.5, 'D' => 81.0]);

    expect($sheet->getCell('A5')->getValue())->toBe('الإجمالي');
    expect((float) $sheet->getCell('C5')->getValue())->toBe(300.5);
    expect((float) $sheet->getCell('D5')->getValue())->toBe(81.0);

    expect($sheet->getStyle('C5')->getNumberFormat()->getFormatCode())->toBe('#,##0.00');
    expect($sheet->getStyle('D5')->getNumberFormat()->getFormatCode())->toBe('#,##0.00');

    // Bold + emerald-tinted footer, reusing the same brand fill finalize() uses for zebra rows.
    expect($sheet->getStyle('A5')->getFont()->getBold())->toBeTrue();
    expect($sheet->getStyle('C5')->getFont()->getBold())->toBeTrue();
    expect($sheet->getStyle('A5')->getFill()->getFillType())->toBe(Fill::FILL_SOLID);
    expect($sheet->getStyle('A5')->getFill()->getStartColor()->getARGB())->toBe('FF'.ExcelReportStyler::ZEBRA);

    // Round-trip through the real xlsx writer/reader — proves the footer survives a save/reload.
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_totals_test_').'.xlsx';
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save($tmpFile);
    expect(file_exists($tmpFile))->toBeTrue();

    $reloaded = (new XlsxReader())->load($tmpFile);
    $reloadedSheet = $reloaded->getActiveSheet();
    expect($reloadedSheet->getCell('A5')->getValue())->toBe('الإجمالي');
    expect((float) $reloadedSheet->getCell('C5')->getValue())->toBe(300.5);
    expect((float) $reloadedSheet->getCell('D5')->getValue())->toBe(81.0);

    unlink($tmpFile);
});

test('totalsRow supports a single money column (e.g. the violation export)', function () {
    $ss = ExcelReportStyler::newBook('اختبار عمود واحد');
    $sheet = $ss->getActiveSheet();

    ExcelReportStyler::headerRow($sheet, ['#', 'اسم المحل', 'قيمة المخالفة']);
    $sheet->setCellValue('C3', 500);
    ExcelReportStyler::finalize($sheet, 'C', 3, 3, ['C']);

    ExcelReportStyler::totalsRow($sheet, 4, 'A', 'الإجمالي', ['C' => 500.0]);

    expect($sheet->getCell('A4')->getValue())->toBe('الإجمالي');
    expect((float) $sheet->getCell('C4')->getValue())->toBe(500.0);
    expect($sheet->getStyle('C4')->getFont()->getBold())->toBeTrue();
});

test('raw accumulator sums money-column values the same way each print_*_xlsx loop does', function () {
    // Mirrors the accumulator pattern added to purchase/expense/calculate/financial/violation
    // exports: sum the RAW numeric value per row (not a formatted/rendered cell string), even
    // when a DB driver hands back a numeric string for a decimal column.
    $rows = [
        (object) ['amount' => 100.5],
        (object) ['amount' => '200.25'],
        (object) ['amount' => 49.25],
    ];

    $sum = 0.0;
    foreach ($rows as $row) {
        $sum += (float) $row->amount;
    }

    expect($sum)->toBe(350.0);
});
