<?php

use App\Services\ExcelReportStyler;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

test('excel report styler produces a valid, readable xlsx with the branded layout', function () {
    $ss = ExcelReportStyler::newBook('تقرير اختبار');
    $sheet = $ss->getActiveSheet();

    ExcelReportStyler::titleRow($sheet, 'تقرير اختبار', 'C');
    $lastCol = ExcelReportStyler::headerRow($sheet, ['#', 'الاسم', 'المبلغ']);

    $sheet->setCellValue('A3', 1);
    $sheet->setCellValue('B3', 'صف تجريبي');
    $sheet->setCellValue('C3', 1234.5);

    $lastRow = 3;
    ExcelReportStyler::finalize($sheet, $lastCol, 3, $lastRow, ['C']);

    // Write to a real temp file instead of downloadJson() (which die()s the
    // process) so we can round-trip it through PhpSpreadsheet's own reader —
    // the strongest available proof the bytes are a valid, openable xlsx.
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_styler_test_').'.xlsx';
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save($tmpFile);

    expect(file_exists($tmpFile))->toBeTrue();
    expect(filesize($tmpFile))->toBeGreaterThan(0);

    $reloaded = (new XlsxReader())->load($tmpFile);
    $reloadedSheet = $reloaded->getActiveSheet();

    expect($reloadedSheet->getCell('A1')->getValue())->toBe('تقرير اختبار');
    expect($reloadedSheet->getCell('A2')->getValue())->toBe('#');
    expect($reloadedSheet->getCell('B2')->getValue())->toBe('الاسم');
    expect($reloadedSheet->getCell('C2')->getValue())->toBe('المبلغ');
    expect($reloadedSheet->getCell('B3')->getValue())->toBe('صف تجريبي');
    expect((float) $reloadedSheet->getCell('C3')->getValue())->toBe(1234.5);

    unlink($tmpFile);
});
