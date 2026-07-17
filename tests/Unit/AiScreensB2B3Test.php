<?php

// Spec 005 B2/B3 — mobile responsiveness + accessibility for the AI-heavy screens, and
// batch-progress clarity on the invoice results page. Verifies (per the task's hard rules):
//   1. Every original id/route/script marker is still present (nothing removed/renamed).
//   2. B2: wide tables are wrapped in .table-responsive; AI file inputs/drop-zones are
//      keyboard/screen-reader reachable (label+id, role="button", tabindex="0", aria-label,
//      :focus-visible outline); AI cards/widgets keep flex-wrap so they stack on mobile.
//   3. B3: invoices/show.blade.php gets a taller/clearer progress bar with a prominent
//      "processing X/N pages" banner, a striped/animated (reduced-motion-safe) processing
//      state, and a green "done" state — wired via a NEW, separate <script> that only
//      *observes* the existing #st/#bar elements, without touching render()/poll().
//   4. Each edited file still compiles cleanly via php -l and Laravel's real BladeCompiler.
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

$projectRoot = dirname(__DIR__, 2);

// ---------------------------------------------------------------------------
// Group A: the 7 AI upload widgets/inline blocks (moraslat, purchase, expense,
// shop, workers, vehicles, manager) — each has an .ai-dropzone file-drop target.
// ---------------------------------------------------------------------------
$dropzones = [
    'resources/views/dashboard/moraslat/_ai_widget.blade.php' => [
        'zoneId' => 'ai_letter_dropzone',
        'inputId' => 'ai_letter',
        'guards' => ["id=\"ai_analyze_btn\"", "id=\"ai_letter_filename\"", "route('dashboard.moraslat.ai_analyze')", "route('dashboard.moraslat.ai_draft')"],
    ],
    'resources/views/dashboard/purchase/_ai_widget.blade.php' => [
        'zoneId' => 'ai_invoice_dropzone',
        'inputId' => 'ai_invoice',
        'guards' => ["id=\"ai_purchase_extract_btn\"", "id=\"ai_invoice_filename\"", "route('dashboard.purchase.ai_extract')"],
    ],
    'resources/views/dashboard/expense/expense_workall.blade.php' => [
        'zoneId' => 'ai_receipt_dropzone',
        'inputId' => 'ai_receipt',
        'guards' => ["id=\"ai_extract_btn\"", "id=\"ai_receipt_filename\"", "route('dashboard.expense.ai_extract')"],
    ],
    'resources/views/dashboard/shop/upd_file.blade.php' => [
        'zoneId' => 'ai_shop_document_dropzone',
        'inputId' => 'ai_shop_document',
        'guards' => ["id=\"ai_shop_extract_btn\"", "id=\"ai_shop_document_filename\"", "route('dashboard.shop.ai_extract')"],
    ],
    'resources/views/dashboard/workers/index.blade.php' => [
        'zoneId' => 'ai_worker_document_dropzone',
        'inputId' => 'ai_worker_document',
        'guards' => ["id=\"ai_worker_extract_btn\"", "id=\"ai_worker_document_filename\"", "route('dashboard.workers.ai_extract')"],
    ],
    'resources/views/vehicles/add.blade.php' => [
        'zoneId' => 'ai_vehicle_document_dropzone',
        'inputId' => 'ai_vehicle_document',
        'guards' => ["id=\"ai_vehicle_extract_btn\"", "id=\"ai_vehicle_document_filename\"", 'route("vehicles.ai_extract")'],
    ],
    'resources/views/dashboard/manager/index.blade.php' => [
        'zoneId' => 'ai_manager_document_dropzone',
        'inputId' => 'ai_manager_document',
        'guards' => ["id=\"ai_manager_extract_btn\"", "id=\"ai_manager_document_filename\"", "route('dashboard.manager.ai_extract')"],
    ],
];

foreach ($dropzones as $relPath => $expect) {
    $path = $projectRoot.'/'.$relPath;

    it("keeps original ids/routes intact in {$relPath}", function () use ($path, $expect) {
        $contents = file_get_contents($path);
        expect($contents)->not->toBeFalse();
        foreach ($expect['guards'] as $needle) {
            expect($contents)->toContain($needle);
        }
        // The dropzone + input + their label-for/id pairing must be untouched.
        expect($contents)->toContain('id="'.$expect['zoneId'].'"');
        expect($contents)->toContain('id="'.$expect['inputId'].'"');
        expect($contents)->toContain('for="'.$expect['inputId'].'"');
    });

    it("makes the drop-zone keyboard/screen-reader reachable in {$relPath}", function () use ($path, $expect) {
        $contents = file_get_contents($path);

        // The dropzone container itself must be an accessible, focusable target.
        expect($contents)->toMatch('/<div class="ai-dropzone" id="'.preg_quote($expect['zoneId'], '/').'"[^>]*role="button"[^>]*>/');
        expect($contents)->toMatch('/<div class="ai-dropzone" id="'.preg_quote($expect['zoneId'], '/').'"[^>]*tabindex="0"[^>]*>/');
        expect($contents)->toMatch('/<div class="ai-dropzone" id="'.preg_quote($expect['zoneId'], '/').'"[^>]*aria-label="[^"]+"[^>]*>/');

        // A visible :focus-visible outline exists for keyboard users.
        expect($contents)->toContain('.ai-dropzone:focus-visible');

        // New additive keyboard-activation script (Enter/Space triggers the file picker)
        // — must not redefine/replace the existing enhance()/aiMotion() functions.
        expect($contents)->toContain('aiDropzoneKeyboardActivate');
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

// ---------------------------------------------------------------------------
// Group B: invoices/leases screens — table-responsive wrapping + guard originals.
// ---------------------------------------------------------------------------
$screens = [
    'resources/views/dashboard/invoices/show.blade.php' => [
        'guards' => ['id="st"', 'id="bar"', 'id="meta"', 'id="grand"', 'id="rescanBtn"', 'id="pushBtn"', 'id="rows"',
            "route('dashboard.invoices.status', \$batch->id)", "route('dashboard.invoices.push', \$batch->id)",
            "route('dashboard.invoices.rescan', \$batch->id)", 'function render(d) {', 'function poll() {'],
    ],
    'resources/views/dashboard/invoices/review.blade.php' => [
        'guards' => ['act-approve', 'act-reject', 'act-reprocess', 'act-draft', 'class="edit"'],
    ],
    'resources/views/dashboard/invoices/report.blade.php' => [
        'guards' => ['kt_chartjs_suppliers', 'kt_chartjs_status'],
    ],
    'resources/views/dashboard/invoices/error.blade.php' => [
        'guards' => ['act-reprocess', 'manual-entry-form', 'act-reprocess-batch'],
    ],
    'resources/views/dashboard/leases/analytics.blade.php' => [
        'guards' => ['statusChart', 'collectionChart', 'forecastChart'],
    ],
    'resources/views/dashboard/leases/unprocessed.blade.php' => [
        'guards' => ['reprocessBtn', "route('dashboard.leases.show', \$e->batch_id)"],
    ],
    'resources/views/dashboard/leases/show.blade.php' => [
        'guards' => ['id="st"', 'id="bar"', 'id="meta"', 'id="rows"', "route('dashboard.leases.status', \$batch->id)", 'function render(d) {', 'function poll() {'],
    ],
];

foreach ($screens as $relPath => $expect) {
    $path = $projectRoot.'/'.$relPath;

    it("keeps original ids/routes/markers intact in {$relPath}", function () use ($path, $expect) {
        $contents = file_get_contents($path);
        expect($contents)->not->toBeFalse();
        foreach ($expect['guards'] as $needle) {
            expect($contents)->toContain($needle);
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

it('wraps the field-confidence table in invoices/review.blade.php with .table-responsive', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/invoices/review.blade.php');
    // Original ids/classes for this screen must remain untouched.
    foreach (['act-approve', 'act-reject', 'act-reprocess', 'act-draft', 'class="edit"'] as $needle) {
        expect($contents)->toContain($needle);
    }
    expect($contents)->toMatch('/<div class="table-responsive">\s*<table class="table table-row-dashed table-sm align-middle">/');
});

it('wraps all three data tables in leases/analytics.blade.php with .table-responsive', function () use ($projectRoot) {
    $contents = file_get_contents($projectRoot.'/resources/views/dashboard/leases/analytics.blade.php');
    // Every original table (collection-history, top tenants, late tenants) is now scrollable.
    expect(substr_count($contents, '<div class="table-responsive">'))->toBeGreaterThanOrEqual(3);
});

// ---------------------------------------------------------------------------
// B3 — batch progress clarity on invoices/show.blade.php ONLY.
// ---------------------------------------------------------------------------
it('adds a taller, clearer, additive progress bar to invoices/show.blade.php without touching render()/poll()', function () use ($projectRoot) {
    $path = $projectRoot.'/resources/views/dashboard/invoices/show.blade.php';
    $contents = file_get_contents($path);

    // Original render()/poll() function bodies must be byte-identical (spot-check key lines).
    expect($contents)->toContain("\$('#bar').css('width', d.percent + '%').text(d.percent + '%');");
    expect($contents)->toContain("\$('#meta').text((d.processed_pages || 0) + ' / ' + (d.total_pages || 0) + ' صفحة'");
    expect($contents)->toContain('if (d.status == \'done\' || d.status == \'failed\') { clearInterval(timer); }');
    expect($contents)->toContain('poll();');
    expect($contents)->toContain('timer = setInterval(poll, 3000);');

    // New CSS: taller bar, striped/animated processing state, green done state.
    expect($contents)->toContain('.ai-progress-tall');
    expect($contents)->toContain('.ai-progress-processing');
    expect($contents)->toContain('.ai-progress-done');
    expect($contents)->toContain('@keyframes ai-progress-stripes');

    // Reduced-motion safety for the new stripe animation.
    preg_match('/@media \(prefers-reduced-motion: reduce\)\s*\{(.*?)\n\s*\}/s', $contents, $m);
    expect($m)->not->toBeEmpty();
    expect($m[1] ?? '')->toContain('ai-progress-processing');

    // New, SEPARATE observer script (does not redefine render/poll — it only reads #st/#bar).
    expect($contents)->toContain('aiProgressBanner');
    expect(substr_count($contents, 'function render(d) {'))->toBe(1);
    expect(substr_count($contents, 'function poll() {'))->toBe(1);
});
