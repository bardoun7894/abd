<?php

namespace App\Services;

/**
 * Rasterizes each PDF page to a PNG image via poppler's `pdftoppm`.
 *
 * Why images, not FPDI sub-PDFs: FPDI re-wraps each page as a form XObject that
 * the cheap Gemini models often render blank. A rasterized PNG renders reliably
 * on every model AND becomes the per-invoice attachment (one image per invoice,
 * not the whole source PDF).
 */
class PdfPageRasterizer
{
    /** Is pdftoppm (poppler) on the PATH? */
    public function available(): bool
    {
        exec('command -v pdftoppm 2>/dev/null', $out, $code);

        return $code === 0 && ! empty($out);
    }

    /**
     * Convert each page of $pdfPath to a PNG under $outDir. Returns the ordered
     * list of PNG paths (page-1.png, page-2.png, …). Throws PdfSplitException so
     * the pipeline can fall back to whole-document mode.
     *
     * Pass $firstPage/$lastPage to render only a page range — the interactive
     * doc-prep path uses (1, 1) so a 30-page scan doesn't rasterize 29 pages
     * that get deleted unread.
     */
    public function rasterize(string $pdfPath, string $outDir, ?int $dpi = null, ?int $firstPage = null, ?int $lastPage = null): array
    {
        if (! $this->available()) {
            throw new PdfSplitException('pdftoppm (poppler) is not available');
        }
        if (! is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }

        $dpi = $dpi ?: (int) config('services.gemini.raster_dpi', 200);
        $timeout = (int) config('services.gemini.page_timeout', 120);
        $prefix = $outDir.'/page';

        $range = '';
        if ($firstPage !== null && $lastPage !== null) {
            $range = sprintf(' -f %d -l %d', $firstPage, $lastPage);
        }

        $cmd = sprintf(
            'timeout %d pdftoppm -png -r %d%s %s %s 2>&1',
            $timeout,
            $dpi,
            $range,
            escapeshellarg($pdfPath),
            escapeshellarg($prefix)
        );
        $output = [];
        $code = 0;
        $this->exec($cmd, $output, $code);
        if ($code === 124) {
            throw new PdfSplitException('pdftoppm timed out after '.$timeout.' seconds');
        }
        if ($code !== 0) {
            throw new PdfSplitException('pdftoppm failed: '.implode("\n", $output));
        }

        $files = glob($prefix.'-*.png') ?: [];
        natsort($files); // page-1, page-2, … page-10 in numeric order

        if (empty($files)) {
            throw new PdfSplitException('pdftoppm produced no pages');
        }

        return array_values($files);
    }

    /** Wrapper around PHP exec so tests can intercept the shell call. */
    protected function exec(string $cmd, array &$output, int &$code): void
    {
        exec($cmd, $output, $code);
    }
}
