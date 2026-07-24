<?php

/**
 * Spec 024 F3 — manual, unique shop-code input on the shop create form
 * (dashboard/shop/index.blade.php) and edit form (dashboard/shop/upd_shop.blade.php).
 *
 * Source-level, mirroring ShopListRestyleTest.php: both forms submit via the
 * shared shop_j.js AJAX flow (FormData + generic $.each(resp.message, ...)
 * error rendering into #displayErrors_shop) rather than a classic
 * redirect-back-with-$errors round trip, so a plain `name="shop_code"` input
 * is both necessary and sufficient for the field to reach the backend and for
 * any future validation message to surface automatically.
 */
uses(Tests\TestCase::class);

function shopCreateFormSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/shop/index.blade.php'));
}

function shopEditFormSource(): string
{
    return file_get_contents(base_path('resources/views/dashboard/shop/upd_shop.blade.php'));
}

it('adds a labeled shop_code input to the shop create form', function () {
    $src = shopCreateFormSource();

    expect($src)->toContain('name="shop_code"');
    expect($src)->toContain('id="shop_code"');
    expect($src)->toContain('كود المحل');
    expect($src)->toContain('placeholder="A1"');
});

it('adds a labeled, prefilled shop_code input to the shop edit form', function () {
    $src = shopEditFormSource();

    expect($src)->toContain('name="shop_code"');
    expect($src)->toContain('id="shop_code"');
    expect($src)->toContain('كود المحل');
    expect($src)->toContain('{{ $shop->shop_code ?? \'\' }}');
});
