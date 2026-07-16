<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Splits a multi-invoice PDF into one single-page PDF per page using FPDI
 * (pure PHP — no Imagick/Ghostscript needed, works on shared hosting).
 * Each single-page PDF is then sent to Gemini as native application/pdf.
 *
 * Throws PdfSplitException when the source PDF can't be parsed (e.g. compressed
 * xref streams that the free FPDI parser rejects); the caller falls back to
 * whole-document extraction.
 */
class PdfPageSplitter
{
    /** Number of pages, or throws PdfSplitException if unreadable. */
    public function pageCount(string $pdfPath): int
    {
        try {
            $pdf = new Fpdi();
            $count = $pdf->setSourceFile($pdfPath);
        } catch (\Throwable $e) {
            throw new PdfSplitException('Cannot read PDF: '.$e->getMessage(), 0, $e);
        }

        return $count;
    }

    /**
     * Write one single-page PDF per page into $destDir.
     *
     * @return string[] absolute paths of the per-page PDFs (page order)
     *
     * @throws PdfSplitException
     */
    public function split(string $pdfPath, string $destDir): array
    {
        if (! is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $count = $this->pageCount($pdfPath);
        $paths = [];

        for ($n = 1; $n <= $count; $n++) {
            try {
                $pdf = new Fpdi();
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetAutoPageBreak(false, 0);
                $pdf->SetMargins(0, 0, 0);
                $pdf->setSourceFile($pdfPath);

                $tpl = $pdf->importPage($n);
                $size = $pdf->getTemplateSize($tpl);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);

                $out = rtrim($destDir, '/').'/page_'.$n.'.pdf';
                $pdf->Output($out, 'F');
                $paths[] = $out;
            } catch (\Throwable $e) {
                throw new PdfSplitException("Failed splitting page {$n}: ".$e->getMessage(), 0, $e);
            }
        }

        return $paths;
    }
}
