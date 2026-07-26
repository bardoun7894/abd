<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Magic-number checks for uploaded documents.
 *
 * WHY (client feedback 2026-07-26: "ظهرت رسالة تقول إنه ليس ملف PDF، مع أنه هو
 * ملف PDF"): Laravel's `mimes:pdf` rule does NOT read the browser's Content-Type.
 * It asks Symfony's MimeTypes guesser, which delegates to the `fileinfo`
 * extension and its mime database. That indirection has two failure modes on
 * shared hosting, and both reject a perfectly valid PDF:
 *
 *   - `fileinfo` is missing or its magic database is stale//trimmed, so the
 *     guesser returns application/octet-stream.
 *   - The upload arrives truncated (a proxy/timeout cut it short), so the
 *     guesser sees a headless fragment.
 *
 * The file's own leading bytes are the authority the format spec actually
 * defines, they need no extension loaded, and they cannot disagree with
 * themselves. So we check those instead and say something true either way.
 */
class UploadSignature
{
    /** Leading bytes that identify each accepted format. */
    private const PDF_MAGIC = '%PDF-';

    /**
     * True when the file begins with the PDF header (%PDF-).
     *
     * Per ISO 32000 the header is the first line of the file. Some generators
     * emit a few junk bytes before it and readers tolerate that, so we allow the
     * marker anywhere in the first 1KB rather than demanding offset 0.
     */
    public static function isPdf(UploadedFile $file): bool
    {
        return str_contains(self::head($file, 1024), self::PDF_MAGIC);
    }

    /** True when the file begins with a JPEG / PNG / GIF / WEBP signature. */
    public static function isImage(UploadedFile $file): bool
    {
        $head = self::head($file, 12);

        return str_starts_with($head, "\xFF\xD8\xFF")                         // JPEG
            || str_starts_with($head, "\x89PNG\r\n\x1a\n")                    // PNG
            || str_starts_with($head, 'GIF87a') || str_starts_with($head, 'GIF89a')
            || (str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP');
    }

    /** True for a PDF or any supported scanned-image format. */
    public static function isPdfOrImage(UploadedFile $file): bool
    {
        return self::isPdf($file) || self::isImage($file);
    }

    /** First $length bytes of the upload, or '' when it cannot be read. */
    private static function head(UploadedFile $file, int $length): string
    {
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            return '';
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return '';
        }

        $bytes = (string) fread($handle, $length);
        fclose($handle);

        return $bytes;
    }
}
