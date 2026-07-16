<?php

// Spec 005 T-C1 — additive, opt-in encrypted document storage. Boots the app
// (Http facade + container + config) but does NOT use RefreshDatabase — pure
// filesystem round-trip, no DB involved.
uses(Tests\TestCase::class);

use App\Services\DocumentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

function documentsPrivateRoot(): string
{
    return storage_path('app/private/uploads/__test_module/ai');
}

function documentsLegacyRoot(): string
{
    return public_path('uploads/__test_module/ai');
}

afterEach(function () {
    File::deleteDirectory(dirname(documentsPrivateRoot()));
    File::deleteDirectory(dirname(documentsLegacyRoot()));
});

it('rejects a module that is not on the allowed whitelist', function () {
    config()->set('documents.allowed_modules', ['expense']);

    $storage = app(DocumentStorage::class);

    expect(fn () => $storage->read('not_whitelisted', 'x.pdf'))
        ->toThrow(\RuntimeException::class);
});

it('stores plaintext outside the public web root when encryption is off', function () {
    config()->set('documents.allowed_modules', ['__test_module']);
    config()->set('documents.encrypt_at_rest', false);

    $file = UploadedFile::fake()->createWithContent('receipt.pdf', '%PDF-1.4 fake receipt bytes');

    $storage = app(DocumentStorage::class);
    $stored = $storage->store($file, '__test_module');

    expect($stored['encrypted'])->toBeFalse();

    $onDisk = documentsPrivateRoot().'/'.$stored['filename'];
    expect(is_file($onDisk))->toBeTrue();
    // Not under the public web root.
    expect(str_starts_with($onDisk, public_path()))->toBeFalse();
    expect(file_get_contents($onDisk))->toBe('%PDF-1.4 fake receipt bytes');

    $read = $storage->read('__test_module', $stored['filename']);
    expect($read['contents'])->toBe('%PDF-1.4 fake receipt bytes');
});

it('encrypts on store and round-trips the exact bytes on read', function () {
    config()->set('documents.allowed_modules', ['__test_module']);
    config()->set('documents.encrypt_at_rest', true);

    $original = '%PDF-1.4 fake receipt bytes for encryption round trip';
    $file = UploadedFile::fake()->createWithContent('receipt.pdf', $original);

    $storage = app(DocumentStorage::class);
    $stored = $storage->store($file, '__test_module');

    expect($stored['encrypted'])->toBeTrue();
    expect($stored['filename'])->toEndWith('.enc');

    $onDisk = documentsPrivateRoot().'/'.$stored['filename'];
    $rawOnDisk = file_get_contents($onDisk);
    // The bytes on disk must NOT be the plaintext — proves it is actually encrypted.
    expect($rawOnDisk)->not->toBe($original);
    expect($rawOnDisk)->not->toContain('fake receipt bytes');

    $read = $storage->read('__test_module', $stored['filename']);
    expect($read['contents'])->toBe($original);
});

it('falls back to legacy plaintext public path for backward compatibility', function () {
    config()->set('documents.allowed_modules', ['__test_module']);
    config()->set('documents.encrypt_at_rest', false);

    // Simulate a file written by an existing (unmigrated) AI controller
    // under public_path('uploads/<module>/ai/<file>') before this service existed.
    @mkdir(documentsLegacyRoot(), 0775, true);
    file_put_contents(documentsLegacyRoot().'/legacy_file.jpg', 'legacy plaintext bytes');

    $storage = app(DocumentStorage::class);
    $read = $storage->read('__test_module', 'legacy_file.jpg');

    expect($read['contents'])->toBe('legacy plaintext bytes');
});

it('throws when the document does not exist in either location', function () {
    config()->set('documents.allowed_modules', ['__test_module']);

    $storage = app(DocumentStorage::class);

    expect(fn () => $storage->read('__test_module', 'nonexistent.pdf'))
        ->toThrow(\RuntimeException::class);
});

it('sanitizes the filename to prevent path traversal', function () {
    config()->set('documents.allowed_modules', ['__test_module']);

    $storage = app(DocumentStorage::class);

    expect(fn () => $storage->read('__test_module', '../../../../etc/passwd'))
        ->toThrow(\RuntimeException::class);
});
