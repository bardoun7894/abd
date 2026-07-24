<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Shared "professional emerald" Excel styler for every print_*_xlsx export.
 *
 * Single source of truth for the صباح النور branded look first shipped on the
 * invoices extraction-log export (InvoiceController::exportBatches). Any report
 * export applies the identical style with three calls:
 *
 *     $ss    = ExcelReportStyler::newBook('تقرير مصاريف شراء sheet-title');
 *     $sheet = $ss->getActiveSheet();
 *     ExcelReportStyler::titleRow($sheet, 'تقرير مصاريف شراء', 'I');
 *     ExcelReportStyler::headerRow($sheet, ['#', 'رقم الفاتورة', ...]); // row 2
 *     // ... existing data loop writing rows 3..N, columns A..I, UNCHANGED ...
 *     ExcelReportStyler::finalize($sheet, 'I', 3, $lastRow, ['D']); // amount col(s)
 *     ExcelReportStyler::downloadJson($ss); // never returns — same base64-JSON contract
 *
 * The styler ONLY touches presentation (fills, borders, fonts, zebra, number
 * format, page setup). Callers keep their own queries, columns, headers and
 * data loops verbatim, and keep the exact base64-JSON download the AJAX
 * frontend expects.
 */
class ExcelReportStyler
{
    /** Brand palette — صباح النور emerald. */
    public const EMERALD = '1B8A5A';       // header row fill

    public const EMERALD_DEEP = '116149';  // title row fill

    public const ZEBRA = 'EAF6F0';         // even data-row fill

    public const BORDER = 'CBD5D1';        // thin data borders

    public const WHITE = 'FFFFFFFF';

    /**
     * A branded, RTL workbook with the Calibri default font and NourSabah
     * document properties. The active sheet is returned RTL-ready; pass an
     * optional sheet title.
     */
    public static function newBook(string $sheetTitle = 'NourSabah'): Spreadsheet
    {
        $ss = new Spreadsheet();

        $props = $ss->getProperties();
        $props->setCreator('NourSabah')
            ->setLastModifiedBy('NourSabah')
            ->setTitle('NourSabah')
            ->setSubject('NourSabah')
            ->setDescription('NourSabah');

        $sheet = $ss->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle(self::safeTitle($sheetTitle));

        $ss->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $ss->setActiveSheetIndex(0);

        return $ss;
    }

    /**
     * Row 1 — merged emerald-deep brand title spanning A1:{lastCol}1.
     * White bold 15pt, centered, 30px tall.
     */
    public static function titleRow(Worksheet $sheet, string $title, string $lastCol): void
    {
        $range = "A1:{$lastCol}1";
        $sheet->mergeCells($range);
        $sheet->setCellValue('A1', $title);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(15)
            ->getColor()->setARGB(self::WHITE);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::EMERALD_DEEP);
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Header row (default row 2). Writes $headers left-to-right starting at
     * column A, then applies the emerald header style across the span.
     * Returns the last column letter (handy for finalize / titleRow spans).
     */
    public static function headerRow(Worksheet $sheet, array $headers, int $row = 2): string
    {
        $headers = array_values($headers);
        $lastCol = Coordinate::stringFromColumnIndex(max(1, count($headers)));

        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, $row, $h);
            $colIdx++;
        }

        $range = "A{$row}:{$lastCol}{$row}";
        $sheet->getRowDimension($row)->setRowHeight(22);
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(11)
            ->getColor()->setARGB(self::WHITE);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::EMERALD);
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        return $lastCol;
    }

    /**
     * Finish the data block and the sheet chrome:
     *   - zebra fill on even data rows
     *   - thin CBD5D1 borders across the data block
     *   - #,##0.00 number format on each column letter in $amountCols
     *   - medium emerald outline around header + data
     *   - autosize A..$lastCol, freeze above $firstDataRow
     *   - landscape A4, fit-to-width, tidy margins, zoom 85, gridlines off
     *
     * Safe when there are zero data rows ($lastRow < $firstDataRow): only the
     * header outline and page setup are applied.
     *
     * @param  string[]  $amountCols  column letters to format as money, e.g. ['D'] or ['F','G','H']
     */
    public static function finalize(
        Worksheet $sheet,
        string $lastCol,
        int $firstDataRow,
        int $lastRow,
        array $amountCols = []
    ): void {
        $headerRow = max(1, $firstDataRow - 1);
        $hasData = $lastRow >= $firstDataRow;

        if ($hasData) {
            // Zebra — even rows get the tint (matches the reference $row % 2 === 0).
            for ($r = $firstDataRow; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::ZEBRA);
                }
            }

            // Thin borders across the data block.
            $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastRow}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB(self::BORDER);

            // Money formatting on requested columns.
            foreach ($amountCols as $col) {
                $sheet->getStyle("{$col}{$firstDataRow}:{$col}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        // Medium emerald outline around header + data (header row when empty).
        $outlineLast = $hasData ? $lastRow : $headerRow;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$outlineLast}")->getBorders()
            ->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)
            ->getColor()->setARGB(self::EMERALD);

        // Autosize every column in the span.
        $lastColIdx = Coordinate::columnIndexFromString($lastCol);
        for ($c = 1; $c <= $lastColIdx; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        // Freeze the title + header rows.
        $sheet->freezePane("A{$firstDataRow}");

        self::pageSetup($sheet);
    }

    /**
     * Report-style page setup: landscape A4, fit one page wide, tidy margins,
     * print-ready zoom, screen gridlines off. Call directly for extra sheets.
     */
    public static function pageSetup(Worksheet $sheet): void
    {
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.3)->setRight(0.3);
        $sheet->getSheetView()->setZoomScale(85);
        $sheet->setShowGridlines(false);
    }

    /**
     * Reproduce the EXACT base64-JSON download the AJAX frontend expects.
     * Renders the workbook to php://output through an output buffer and emits
     * {"op":"ok","file":"data:application/vnd.ms-excel;base64,..."} then die()s.
     *
     * @return never
     */
    public static function downloadJson(Spreadsheet $ss, string $filename = 'myfile.xlsx'): void
    {
        ob_start();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        (new Xlsx($ss))->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        $response = [
            'op' => 'ok',
            'file' => 'data:application/vnd.ms-excel;base64,'.base64_encode($xlsData),
        ];
        die(json_encode($response));
    }

    /** Excel sheet titles cannot exceed 31 chars or contain []*?:/\ . */
    private static function safeTitle(string $title): string
    {
        $title = str_replace(['[', ']', '*', '?', ':', '/', '\\'], ' ', $title);
        $title = trim($title) === '' ? 'Sheet1' : $title;

        return mb_substr($title, 0, 31);
    }
}
