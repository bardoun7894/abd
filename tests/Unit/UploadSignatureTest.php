<?php

use App\Support\UploadSignature;
use Illuminate\Http\UploadedFile;

uses(Tests\TestCase::class);

/**
 * Regression cover for the 2026-07-26 client report: a valid PDF was refused with
 * "ليس ملف PDF". The old rule (`mimes:pdf`) asked the `fileinfo` extension what
 * the file was; these cases pin the replacement to the format's own magic number,
 * which needs no extension loaded and cannot disagree with the file itself.
 */
function tmpUpload(string $bytes, string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'sigtest');
    file_put_contents($path, $bytes);

    return new UploadedFile($path, $name, null, null, true);
}

it('accepts a real PDF', function () {
    $pdf = tmpUpload("%PDF-1.5\n%\xE2\xE3\xCF\xD3\n1 0 obj\n", 'invoice.pdf');

    expect(UploadSignature::isPdf($pdf))->toBeTrue()
        ->and(UploadSignature::isPdfOrImage($pdf))->toBeTrue();
});

it('accepts a PDF whose header is preceded by junk bytes', function () {
    // Some generators emit a BOM or stray newlines first; readers tolerate it,
    // so refusing the upload would be stricter than the format itself.
    $pdf = tmpUpload("\xEF\xBB\xBF\r\n%PDF-1.4\n", 'scanned.pdf');

    expect(UploadSignature::isPdf($pdf))->toBeTrue();
});

it('rejects a non-PDF that was merely renamed to .pdf', function () {
    $fake = tmpUpload('this is plain text, not a document', 'renamed.pdf');

    expect(UploadSignature::isPdf($fake))->toBeFalse()
        ->and(UploadSignature::isPdfOrImage($fake))->toBeFalse();
});

it('recognises the scanned-image formats the invoice upload accepts', function () {
    $jpeg = tmpUpload("\xFF\xD8\xFF\xE0".str_repeat("\x00", 12), 'scan.jpg');
    $png = tmpUpload("\x89PNG\r\n\x1a\n".str_repeat("\x00", 12), 'scan.png');
    $webp = tmpUpload('RIFF'."\x00\x00\x00\x00".'WEBP', 'scan.webp');
    $gif = tmpUpload('GIF89a'.str_repeat("\x00", 12), 'scan.gif');

    foreach ([$jpeg, $png, $webp, $gif] as $image) {
        expect(UploadSignature::isImage($image))->toBeTrue()
            ->and(UploadSignature::isPdfOrImage($image))->toBeTrue()
            ->and(UploadSignature::isPdf($image))->toBeFalse();
    }
});

it('treats an empty upload as neither PDF nor image', function () {
    // A truncated upload (proxy/timeout) is exactly the case that used to produce
    // the misleading "not a PDF" message; it must still be refused, but the
    // caller now says so in words the user can act on.
    $empty = tmpUpload('', 'truncated.pdf');

    expect(UploadSignature::isPdfOrImage($empty))->toBeFalse();
});
