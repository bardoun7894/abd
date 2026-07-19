<?php

namespace App\Services;

/**
 * Spec 007 — shrinks the file handed to Gemini for the INTERACTIVE (synchronous
 * form-prefill) extraction path. The needed data lives on page 1 of a scanned
 * document; sending a whole multi-page PDF (or an oversized image) base64-inlined
 * into every request wastes input tokens for nothing.
 *
 * `prepare()` NEVER throws — a prep failure must not break extraction, so any
 * error (missing pdftoppm, corrupt PDF, missing GD, unreadable image) falls back
 * to the original file path untouched.
 */
class InteractiveDocPrep
{
    public function __construct(private PdfPageRasterizer $rasterizer) {}

    /**
     * @param  int  $maxPages  how many leading PDF pages to rasterize (1 = page 1
     *                          only). Shop leases use 3: the EJAR unified contract
     *                          keeps the financial data (rent, payment schedule) on
     *                          page 3, so page-1-only misses the whole rent block.
     * @return array{path:string, paths:array<int,string>, cleanup:callable}
     */
    public function prepare(string $filePath, int $maxPages = 1): array
    {
        $noop = ['path' => $filePath, 'paths' => [$filePath], 'cleanup' => function () {}];

        if (! is_file($filePath)) {
            return $noop;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $prepared = $ext === 'pdf'
            ? $this->preparePdf($filePath, $maxPages)
            : $this->prepareImage($filePath);

        return $prepared ?? $noop;
    }

    /**
     * @return array{path:string, paths:array<int,string>, cleanup:callable}|null
     */
    private function preparePdf(string $filePath, int $maxPages = 1): ?array
    {
        if (! $this->rasterizer->available()) {
            return null;
        }

        try {
            $outDir = sys_get_temp_dir().'/interactive_doc_prep_'.uniqid('', true);
            $dpi = (int) config('services.gemini.interactive_dpi', 130);
            // Only the leading pages are needed — ask poppler for them directly so
            // a long multi-page scan doesn't rasterize pages we would delete unread.
            $pages = $this->rasterizer->rasterize($filePath, $outDir, $dpi, 1, max(1, $maxPages));

            if (empty($pages)) {
                return null;
            }

            return [
                'path' => $pages[0],
                'paths' => array_values($pages),
                'cleanup' => function () use ($outDir) {
                    $this->removeDir($outDir);
                },
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{path:string, cleanup:callable}|null
     */
    private function prepareImage(string $filePath): ?array
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            $info = @getimagesize($filePath);
            if ($info === false) {
                return null;
            }

            [$width, $height, $type] = $info;
            $maxPx = (int) config('services.gemini.interactive_max_px', 1600);
            $largestSide = max($width, $height);

            if ($largestSide <= $maxPx || $width <= 0 || $height <= 0) {
                return null;
            }

            $src = $this->loadImage($filePath, $type);
            if ($src === false) {
                return null;
            }

            $scale = $maxPx / $largestSide;
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $outDir = sys_get_temp_dir().'/interactive_doc_prep_'.uniqid('', true);
            @mkdir($outDir, 0775, true);
            $outPath = $outDir.'/downscaled.png';
            $ok = imagepng($dst, $outPath);

            imagedestroy($src);
            imagedestroy($dst);

            if (! $ok) {
                return null;
            }

            return [
                'path' => $outPath,
                'paths' => [$outPath],
                'cleanup' => function () use ($outDir) {
                    $this->removeDir($outDir);
                },
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return \GdImage|false */
    private function loadImage(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_BMP => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            default => false,
        };
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (glob($dir.'/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }
}
