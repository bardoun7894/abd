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
     * @return array{path:string, cleanup:callable}
     */
    public function prepare(string $filePath): array
    {
        $noop = ['path' => $filePath, 'cleanup' => function () {}];

        if (! is_file($filePath)) {
            return $noop;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $prepared = $ext === 'pdf'
            ? $this->preparePdf($filePath)
            : $this->prepareImage($filePath);

        return $prepared ?? $noop;
    }

    /**
     * @return array{path:string, cleanup:callable}|null
     */
    private function preparePdf(string $filePath): ?array
    {
        if (! $this->rasterizer->available()) {
            return null;
        }

        try {
            $outDir = sys_get_temp_dir().'/interactive_doc_prep_'.uniqid('', true);
            $dpi = (int) config('services.gemini.interactive_dpi', 130);
            $pages = $this->rasterizer->rasterize($filePath, $outDir, $dpi);

            if (empty($pages)) {
                return null;
            }

            $page1 = $pages[0];

            // Only page 1 is needed — delete the rest right away.
            foreach (array_slice($pages, 1) as $extraPage) {
                @unlink($extraPage);
            }

            return [
                'path' => $page1,
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
