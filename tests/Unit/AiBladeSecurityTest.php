<?php

// Regression tests for AI blade XSS + polling UX findings.
// These tests inspect the compiled blade source (not the rendered JS runtime)
// because the defects are in inline JavaScript.

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

function bladePath(string $relative): string
{
    return resource_path("views/{$relative}");
}

function assertNoVariableInnerHtml(string $path, array $vars): void
{
    $full = bladePath($path);
    expect(file_exists($full))->toBeTrue("Missing blade file: {$full}");
    $raw = file_get_contents($full);

    foreach ($vars as $var) {
        // Match innerHTML = ... + var or innerHTML = '...' + var + '...'
        // This catches the previously vulnerable concatenation patterns.
        expect($raw)->not->toMatch(
            '/innerHTML\s*=\s*["\'\`][^"\'\`]*\+?\s*' . preg_quote($var, '/') . '/',
            "{$path} still assigns innerHTML with variable: {$var}"
        );
    }

    $error = null;
    try {
        Blade::compileString($raw);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
    expect($error)->toBeNull("Blade compile failed for {$path}: {$error}");
}

dataset('ai_blade_xss_sinks', [
    'report/index' => [
        'path' => 'dashboard/report/index.blade.php',
        'vars' => ['res.message_out'],
    ],
    'purchase/_ai_widget' => [
        'path' => 'dashboard/purchase/_ai_widget.blade.php',
        'vars' => ['res.message_out', 'f.name', 'd.needs_review'],
    ],
    'moraslat/_ai_widget' => [
        'path' => 'dashboard/moraslat/_ai_widget.blade.php',
        'vars' => ['res.message_out', 'f.name', 'd.category_name', 'd.type_name'],
    ],
    'expense/expense_workall' => [
        'path' => 'dashboard/expense/expense_workall.blade.php',
        'vars' => ['res.message_out', 's.error', 'f.name', 'd.category_name'],
    ],
    'workers/index' => [
        'path' => 'dashboard/workers/index.blade.php',
        'vars' => ['res.message_out', 's.error', 'f.name', 'd.nationality_name'],
    ],
    'manager/index' => [
        'path' => 'dashboard/manager/index.blade.php',
        'vars' => ['res.message_out', 's.error', 'f.name'],
    ],
    'shop/upd_file' => [
        'path' => 'dashboard/shop/upd_file.blade.php',
        'vars' => ['res.message_out', 's.error', 'f.name', 'd.owner_name', 'd.rent_amount'],
    ],
    'violation/_ai_widget' => [
        'path' => 'dashboard/violation/_ai_widget.blade.php',
        'vars' => ['d.side', 'd.severity', 'd.suggested_action'],
    ],
]);

test('AI blades do not use innerHTML with server/AI/user-controlled variables', function (string $path, array $vars) {
    assertNoVariableInnerHtml($path, $vars);
})->with('ai_blade_xss_sinks');

test('invoices/show poll() has a fail handler so the spinner stops on error', function () {
    $raw = file_get_contents(bladePath('dashboard/invoices/show.blade.php'));

    expect($raw)->toContain('function poll()');

    // Extract from the opening brace of poll() to the matching closing brace
    // by scanning forward and counting braces.
    $start = strpos($raw, 'function poll()');
    $open = strpos($raw, '{', $start);
    $depth = 1;
    $i = $open + 1;
    $len = strlen($raw);
    while ($i < $len && $depth > 0) {
        $c = $raw[$i];
        if ($c === '{') { $depth++; }
        elseif ($c === '}') { $depth--; }
        $i++;
    }
    $body = substr($raw, $open, $i - $open);

    expect($body)->toContain('$.getJSON(statusUrl)')
        ->and($body)->toContain('.done(function')
        ->and($body)->toContain('.fail(function')
        ->and($body)->toContain('clearInterval(timer)');
});

test('shop/upd_file polling stops and surfaces error when status response is falsy', function () {
    $raw = file_get_contents(bladePath('dashboard/shop/upd_file.blade.php'));

    // Locate the status fetch handler `.then(function(s){ ... }).catch(...)`
    // by extracting from `.then(function(s){` to the matching closing `})`.
    $needle = '.then(function(s){';
    $start = strpos($raw, $needle);
    expect($start)->not->toBeFalse('status fetch handler not found');
    $open = $start + strlen($needle);
    $depth = 1;
    $i = $open;
    $len = strlen($raw);
    while ($i < $len && $depth > 0) {
        $c = $raw[$i];
        if ($c === '{') { $depth++; }
        elseif ($c === '}') { $depth--; }
        $i++;
    }
    $block = substr($raw, $start, $i - $start);

    expect($block)->toContain('!s.status')
        ->and($block)->toContain('clearInterval(iv)')
        ->and($block)->toContain('btn.disabled=false')
        ->and($block)->toContain('s.message_out');
});

test('shop/upd_file markExtracted clears tint on select2 change events', function () {
    $raw = file_get_contents(bladePath('dashboard/shop/upd_file.blade.php'));

    // The function must listen to both input and change so select2 edits clear .ai-extracted.
    preg_match('/function markExtracted\(el\)\s*\{([\s\S]*?)\n\s*\}/', $raw, $m);
    $fn = $m[1] ?? '';

    expect($fn)->toContain("addEventListener('input'")
        ->and($fn)->toContain("addEventListener('change'");
});
