<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Spec 005 T-C1 — additive, opt-in encrypted-at-rest storage for AI-document
 * uploads.
 *
 * This is a NEW, separate layer. It does not rewire any existing controller
 * (Expense/Shop/Vehicle/Workers/Moraslat/Purchase AI upload actions, or the
 * invoice/lease pipeline) — those keep writing plaintext to
 * public_path('uploads/<module>/ai') exactly as before. A controller only
 * gets the private + encrypted behaviour once it is explicitly migrated to
 * call DocumentStorage::store()/read() instead of File::move()/file_get_contents().
 *
 * Backward compatibility: read() first checks the new private root
 * (storage/app/private/uploads/<module>/ai/<filename>), and falls back to
 * the legacy public root (public_path('uploads/<module>/ai/<filename>')) so
 * files written before this service existed keep opening correctly.
 */
class DocumentStorage
{
    /**
     * Store an uploaded file for a module. Always writes outside the public
     * web root. Encrypts the bytes only when documents.encrypt_at_rest is
     * enabled (default OFF).
     *
     * @return array{module:string,filename:string,encrypted:bool,mime:?string}
     */
    public function store(UploadedFile $file, string $module): array
    {
        $module = $this->sanitizeModule($module);

        $contents = file_get_contents($file->getRealPath());
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $encrypt = (bool) config('documents.encrypt_at_rest', false);

        $name = Str::random(8).'_'.time().'.'.$ext.($encrypt ? '.enc' : '');

        $dir = $this->privateDir($module);
        if (! is_dir($dir)) {
            @mkdir($dir, 0770, true);
        }

        $payload = $encrypt ? Crypt::encryptString(base64_encode($contents)) : $contents;
        file_put_contents($dir.'/'.$name, $payload);

        return [
            'module' => $module,
            'filename' => $name,
            'encrypted' => $encrypt,
            'mime' => $file->getClientMimeType(),
        ];
    }

    /**
     * Read a document by module + filename. Tries the private (possibly
     * encrypted) root first, then falls back to the legacy plaintext public
     * root for backward compatibility.
     *
     * @return array{contents:string,mime:string}
     */
    public function read(string $module, string $filename): array
    {
        $module = $this->sanitizeModule($module);
        $filename = $this->sanitizeFilename($filename);

        $privatePath = $this->privateDir($module).'/'.$filename;
        if (is_file($privatePath)) {
            $raw = file_get_contents($privatePath);
            if (Str::endsWith($filename, '.enc')) {
                $raw = base64_decode(Crypt::decryptString($raw));
            }

            return ['contents' => $raw, 'mime' => $this->mimeForFilename($filename)];
        }

        $legacyPath = $this->legacyDir($module).'/'.$filename;
        if (is_file($legacyPath)) {
            return ['contents' => file_get_contents($legacyPath), 'mime' => $this->mimeForFilename($filename)];
        }

        throw new \RuntimeException("Document not found: {$module}/{$filename}");
    }

    /**
     * Write the uploaded file to a short-lived plaintext temp path WITH the
     * original extension, so an AI extractor can read it before we store the
     * (possibly encrypted) copy. Caller MUST @unlink the returned path.
     */
    public function tempWorkingCopy(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $tmp = sys_get_temp_dir().'/aidoc_'.Str::random(10).'.'.$ext;
        copy($file->getRealPath(), $tmp);

        return $tmp;
    }

    private function privateDir(string $module): string
    {
        return rtrim(config('documents.private_root'), '/').'/'.$module.'/ai';
    }

    private function legacyDir(string $module): string
    {
        return rtrim(config('documents.legacy_public_root'), '/').'/'.$module.'/ai';
    }

    private function sanitizeModule(string $module): string
    {
        $allowed = config('documents.allowed_modules', []);
        if (! in_array($module, $allowed, true)) {
            throw new \RuntimeException("Document module not allowed: {$module}");
        }

        return $module;
    }

    /**
     * Strip any directory component — the filename must resolve to exactly
     * one path segment inside the module's ai/ directory. Prevents path
     * traversal via ../ or absolute paths.
     */
    private function sanitizeFilename(string $filename): string
    {
        $safe = basename($filename);
        if ($safe === '' || $safe !== $filename || $safe === '.' || $safe === '..') {
            throw new \RuntimeException('Invalid document filename.');
        }

        return $safe;
    }

    private function mimeForFilename(string $filename): string
    {
        $filename = Str::endsWith($filename, '.enc') ? substr($filename, 0, -4) : $filename;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
