<?php

// Spec 005 T-C1 — encrypted-at-rest round trip for the DocumentStorage
// service used by the 6 migrated AI-document upload controllers. Boots the
// app (Http facade + container + config) but does NOT use RefreshDatabase —
// pure filesystem round-trip, no DB involved. Never calls the live Gemini API.
uses(Tests\TestCase::class);

use App\Services\DocumentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(storage_path('app/private/uploads/expense'));
    File::deleteDirectory(public_path('uploads/shop'));
    config(['documents.encrypt_at_rest' => false]);
});

it('stores an encrypted file outside the public root and round-trips the exact bytes on read', function () {
    config(['documents.encrypt_at_rest' => true]);

    $original = 'HELLO-PDF-BYTES';
    $file = UploadedFile::fake()->createWithContent('r.pdf', $original);

    $storage = app(DocumentStorage::class);
    $stored = $storage->store($file, 'expense');

    expect($stored['encrypted'])->toBeTrue();
    expect($stored['filename'])->toEndWith('.enc');

    $onDisk = storage_path('app/private/uploads/expense/ai/'.$stored['filename']);
    expect(is_file($onDisk))->toBeTrue();
    expect(str_starts_with($onDisk, public_path()))->toBeFalse();

    $rawOnDisk = file_get_contents($onDisk);
    expect($rawOnDisk)->not->toBe($original);

    $read = $storage->read('expense', $stored['filename']);
    expect($read['contents'])->toBe($original);
    expect($read['mime'])->toBe('application/pdf');
});

it('falls back to a legacy plaintext file for backward compatibility', function () {
    config(['documents.encrypt_at_rest' => false]);

    $legacyDir = public_path('uploads/shop/ai');
    @mkdir($legacyDir, 0775, true);
    file_put_contents($legacyDir.'/legacy.png', 'legacy plaintext bytes');

    $storage = app(DocumentStorage::class);
    $read = $storage->read('shop', 'legacy.png');

    expect($read['contents'])->toBe('legacy plaintext bytes');
});
