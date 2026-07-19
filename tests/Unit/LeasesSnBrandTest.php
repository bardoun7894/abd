<?php

// Spec 007 UI polish — bring the 3 lease-extraction Blade pages (index/upload/analytics)
// onto the SN emerald brand, same treatment already applied to the invoice pages. Guards:
//   1. Zero off-brand Metronic yellow/hex left in any of the 3 files.
//   2. Every original id/route/canvas-id/data key/script marker is still present.
//   3. Each edited file still compiles cleanly via php -l and Laravel's real BladeCompiler.
//   4. Brand helper classes (sn-thead / sn-row-hover / sn-num / sn-stat) are actually used.
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

$projectRoot = dirname(__DIR__, 2);

$files = [
    'resources/views/dashboard/leases/index.blade.php',
    'resources/views/dashboard/leases/upload.blade.php',
    'resources/views/dashboard/leases/analytics.blade.php',
];

$offBrandNeedles = ['#ffb822', '#fff8e7', '#ffc700', '#50cd89', '#f1416c', '#a1a5b7'];

foreach ($files as $relPath) {
    $path = $projectRoot.'/'.$relPath;

    it("drops every off-brand Metronic color from {$relPath}", function () use ($path, $offBrandNeedles) {
        $contents = file_get_contents($path);
        expect($contents)->not->toBeFalse();
        foreach ($offBrandNeedles as $needle) {
            expect($contents)->not->toContain($needle);
        }
    });

    it("passes php -l and Laravel's real BladeCompiler::compileString() for {$relPath}", function () use ($path) {
        $lint = shell_exec('php -l '.escapeshellarg($path).' 2>&1');
        expect($lint)->toContain('No syntax errors detected');

        $raw = file_get_contents($path);
        $exception = null;
        $compiled = null;
        try {
            $compiled = Blade::compileString($raw);
        } catch (\Throwable $e) {
            $exception = $e;
        }
        expect($exception)->toBeNull();
        expect($compiled)->toBeString();
    });
}

it('keeps every original id/route/data marker intact in leases/index.blade.php', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/index.blade.php');
    foreach ([
        "route('dashboard.leases.create')",
        "route('dashboard.leases.show', \$b->id)",
        '$b->id', '$b->original_filename', '$b->processed_pages', '$b->total_pages',
        '$b->status', '$b->created_at', '@forelse', '@empty', '@endforelse',
    ] as $needle) {
        expect($contents)->toContain($needle);
    }
});

it('applies sn-thead + sn-row-hover to the leases/index.blade.php table', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/index.blade.php');
    expect($contents)->toContain('sn-thead');
    expect($contents)->toContain('sn-row-hover');
});

it('keeps every original id/route/script marker intact in leases/upload.blade.php', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/upload.blade.php');
    foreach ([
        'id="up_form"', 'id="pdf"', 'id="fname"', 'id="drop"', 'id="btn"', 'id="ov"', 'id="err"',
        "route('dashboard.leases.store')", "route('dashboard.leases.index')",
        "dashboard.partials.ai_subscription_banner",
        'function showErr(m)', '$.ajaxSetup(',
        "['dragenter','dragover']", "['dragleave','drop']",
    ] as $needle) {
        expect($contents)->toContain($needle);
    }
});

it('recolors the leases/upload.blade.php dropzone onto SN emerald tokens', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/upload.blade.php');
    expect($contents)->toContain('var(--sn-emerald)');
    expect($contents)->toContain('var(--sn-emerald-tint)');
});

it('keeps every original chart canvas id / data key / table wrap intact in leases/analytics.blade.php', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/analytics.blade.php');
    foreach ([
        'id="statusChart"', 'id="collectionChart"', 'id="forecastChart"',
        "\$stats['active']", "\$stats['ended']", "\$stats['renewable']", "\$stats['troubled']",
        "\$stats['collection_rate']", "\$stats['overdue']", "\$stats['upcoming']", "\$stats['monthly_revenue']",
        "\$stats['due_total']", "\$stats['paid_total']", "\$stats['annual_revenue']",
        '$collection_history', '$top_tenants', '$late_tenants', '$forecast', "\$trend['source']", "\$trend['narrative']",
        '@forelse', '@empty', '@endforelse',
    ] as $needle) {
        expect($contents)->toContain($needle);
    }
    expect(substr_count($contents, '<div class="table-responsive">'))->toBeGreaterThanOrEqual(3);
});

it('upgrades the leases/analytics.blade.php stat tiles to sn-stat and unifies chart card headers', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/analytics.blade.php');
    expect($contents)->toContain('sn-stat');
    expect($contents)->toContain('sn-num');
    expect($contents)->toContain('sn-chart-ico');
});

it('reads leases/analytics.blade.php Chart.js colors from SN CSS variables', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/analytics.blade.php');
    expect($contents)->toContain("KTUtil.getCssVariableValue('--sn-emerald')");
    expect($contents)->toContain('--sn-amber');
    expect($contents)->toContain('--sn-rust');
});
