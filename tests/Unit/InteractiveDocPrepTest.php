<?php

// Boot the Laravel app for these (Http facade + container) but DO NOT use
// RefreshDatabase — the main DB is remote and these tests don't need it.
uses(Tests\TestCase::class);

use App\Services\InteractiveDocPrep;
use App\Services\PdfPageRasterizer;
use App\Services\PdfSplitException;

function tmpImageFile(int $width = 2000, int $height = 1000): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'docprepimg').'.png';
    $img = imagecreatetruecolor($width, $height);
    imagefill($img, 0, 0, imagecolorallocate($img, 255, 0, 0));
    imagepng($img, $tmp);
    imagedestroy($img);

    return $tmp;
}

function tmpPdfFile(): string
{
    $tmp = tempnam(sys_get_temp_dir(), 'docpreppdf').'.pdf';
    file_put_contents($tmp, '%PDF-1.4 fake, not a real pdf');

    return $tmp;
}

it('returns a usable path and callable cleanup for an image input', function () {
    $file = tmpImageFile();

    $result = app(InteractiveDocPrep::class)->prepare($file);

    expect($result)->toHaveKeys(['path', 'cleanup']);
    expect(is_file($result['path']))->toBeTrue();
    expect(is_callable($result['cleanup']))->toBeTrue();

    ($result['cleanup'])();
    @unlink($file);
});

it('downscales an oversized image to the configured max px bound', function () {
    config()->set('services.gemini.interactive_max_px', 500);
    $file = tmpImageFile(2000, 1000);

    $result = app(InteractiveDocPrep::class)->prepare($file);

    [$w, $h] = getimagesize($result['path']);
    expect(max($w, $h))->toBeLessThanOrEqual(500);
    expect($result['path'])->not->toBe($file);

    ($result['cleanup'])();
    @unlink($file);
});

it('does not downscale an image already under the max px bound', function () {
    config()->set('services.gemini.interactive_max_px', 1600);
    $file = tmpImageFile(400, 300);

    $result = app(InteractiveDocPrep::class)->prepare($file);

    expect($result['path'])->toBe($file);

    ($result['cleanup'])();
    @unlink($file);
});

it('rasterizes a PDF and returns only the page-1 image', function () {
    $file = tmpPdfFile();

    $page1 = tempnam(sys_get_temp_dir(), 'page1').'.png';
    $page2 = tempnam(sys_get_temp_dir(), 'page2').'.png';
    file_put_contents($page1, 'fake-png-1');
    file_put_contents($page2, 'fake-png-2');

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('available')->andReturn(true);
    $rasterizer->shouldReceive('rasterize')->once()->andReturn([$page1, $page2]);
    app()->instance(PdfPageRasterizer::class, $rasterizer);

    $result = app(InteractiveDocPrep::class)->prepare($file);

    expect($result['path'])->toBe($page1);
    expect(is_callable($result['cleanup']))->toBeTrue();

    ($result['cleanup'])();
    @unlink($file);
    @unlink($page2);
});

it('falls back to the original file when the rasterizer is unavailable', function () {
    $file = tmpPdfFile();

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('available')->andReturn(false);
    $rasterizer->shouldNotReceive('rasterize');
    app()->instance(PdfPageRasterizer::class, $rasterizer);

    $result = app(InteractiveDocPrep::class)->prepare($file);

    expect($result['path'])->toBe($file);
    expect(is_callable($result['cleanup']))->toBeTrue();

    ($result['cleanup'])();
    @unlink($file);
});

it('falls back to the original file when rasterization throws', function () {
    $file = tmpPdfFile();

    $rasterizer = Mockery::mock(PdfPageRasterizer::class);
    $rasterizer->shouldReceive('available')->andReturn(true);
    $rasterizer->shouldReceive('rasterize')->andThrow(new PdfSplitException('boom'));
    app()->instance(PdfPageRasterizer::class, $rasterizer);

    $result = app(InteractiveDocPrep::class)->prepare($file);

    expect($result['path'])->toBe($file);
    expect(is_callable($result['cleanup']))->toBeTrue();

    ($result['cleanup'])();
    @unlink($file);
});

it('never throws even when the file does not exist', function () {
    $result = app(InteractiveDocPrep::class)->prepare('/nonexistent/path/does-not-exist.pdf');

    expect($result['path'])->toBe('/nonexistent/path/does-not-exist.pdf');
    expect(is_callable($result['cleanup']))->toBeTrue();

    ($result['cleanup'])();
});
