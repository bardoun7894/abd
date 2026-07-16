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
     */
    public function rasterize(string $pdfPath, string $outDir, ?int $dpi = null): array
    {
        if (! $this->available()) {
            throw new PdfSplitException('pdftoppm (poppler) is not available');
        }
        if (! is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }

        $dpi = $dpi ?: (int) config('services.gemini.raster_dpi', 200);
        $prefix = $outDir.'/page';

        $cmd = sprintf(
            'pdftoppm -png -r %d %s %s 2>&1',
            $dpi,
            escapeshellarg($pdfPath),
            escapeshellarg($prefix)
        );
        exec($cmd, $output, $code);
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
}
