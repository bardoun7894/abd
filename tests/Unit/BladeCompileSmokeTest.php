<?php

// Safety net for the UI overhaul: every LIVE blade view must compile via Laravel's
// real BladeCompiler. Cheap (no DB, no HTTP), fast, and it catches @directive / PHP
// syntax breakage across the whole view tree in one shot — exactly the failure mode
// a broad restyle sweep risks. Would have caught the (pre-existing) layouts.dashboard
// reference and any malformed edit.
//
// Dead duplicate files (copy/backup/junk-dir views) are excluded: they are unreachable
// (Blade cannot @include names with spaces; the @@/$$ dirs have zero references) and
// several predate current helpers, so compiling them proves nothing.

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

function liveBladeFiles(): array
{
    $root = dirname(__DIR__, 2).'/resources/views';
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    $files = [];
    foreach ($rii as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        $path = $file->getPathname();
        // skip dead duplicates / junk dirs, and the stock Metronic demo tree under
        // resources/views/pages/** (42 unrouted files referencing a nonexistent
        // <x-base-layout> — confirmed no route/controller references 'pages.').
        if (preg_match('#/pages/#', $path)) {
            continue;
        }
        if (preg_match('/\bcopy\b|\bCopy\b|@@|\$\$|_{4,}/', $path)) {
            continue;
        }
        $files[] = $path;
    }
    sort($files);

    return $files;
}

$files = liveBladeFiles();

it('has a non-trivial number of live views to check', function () use ($files) {
    expect(count($files))->toBeGreaterThan(200);
});

test('every live blade view compiles', function (string $path) {
    $raw = file_get_contents($path);

    $compiled = null;
    $error = null;
    try {
        $compiled = Blade::compileString($raw);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }

    expect($error)->toBeNull("Blade compile failed for {$path}: {$error}");
    expect($compiled)->toBeString();
})->with(array_map(fn ($p) => [$p], $files));
