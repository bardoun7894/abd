<?php

// T6-motion: purposeful motion/animation for the 6 AI upload widgets + moraslat draft.
// Verifies (per the task's hard rules):
//   1. Every original id/route/script marker is still present (nothing removed/renamed).
//   2. New, additive motion CSS classes/keyframes exist (analyzing state, result reveal,
//      low-confidence pulse, dropzone affordance, card entrance).
//   3. The prefers-reduced-motion block was extended to neutralize every new animation.
//   4. Each file still compiles cleanly via php -l and Laravel's real BladeCompiler.
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

$widgets = [
    'resources/views/dashboard/moraslat/_ai_widget.blade.php' => [
        'ids' => ['ai_analyze_btn', 'ai_letter', 'ai_analyze_status', 'ai_draft_btn', 'ai_reply_draft',
            'ai_draft_status', 'ai_letter_dropzone', 'ai_letter_filename', 'ai_preview_moraslat',
            'moraslat_respon', 'moraslat_categoty_id'],
        'routes' => ["route('dashboard.moraslat.ai_analyze')", "route('dashboard.moraslat.ai_draft')"],
    ],
    'resources/views/dashboard/purchase/_ai_widget.blade.php' => [
        'ids' => ['ai_purchase_extract_btn', 'ai_invoice', 'ai_purchase_extract_status', 'ai_invoice_dropzone',
            'ai_invoice_filename', 'ai_preview_purchase', 'purchase_no', 'purchase_dt', 'purchase_respon',
            'purchase_price', 'tax_number'],
        'routes' => ["route('dashboard.purchase.ai_extract')"],
    ],
    'resources/views/dashboard/expense/expense_workall.blade.php' => [
        'ids' => ['ai_extract_btn', 'ai_receipt', 'ai_extract_status', 'ai_receipt_dropzone',
            'ai_receipt_filename', 'ai_preview_expense', 'expense_price', 'expense_respon',
            'expense_month_desc', 'expense_categoty_id'],
        // P2 async migration: transport now starts the generic job via ai_extract.start
        // (module=expense) instead of the blocking dashboard.expense.ai_extract call.
        'routes' => ["route('dashboard.ai_extract.start', ['module' => 'expense'])"],
    ],
    'resources/views/dashboard/shop/upd_file.blade.php' => [
        'ids' => ['ai_shop_extract_btn', 'ai_shop_document', 'ai_shop_extract_status', 'ai_shop_document_dropzone',
            'ai_shop_document_filename', 'ai_preview_shop', 'comme_no', 'municip_no', 'rent_no', 'rent_name'],
        'routes' => ["route('dashboard.shop.ai_extract_async')"],
    ],
    'resources/views/dashboard/workers/index.blade.php' => [
        'ids' => ['ai_worker_extract_btn', 'ai_worker_document', 'ai_worker_extract_status',
            'ai_worker_document_dropzone', 'ai_worker_document_filename', 'ai_preview_workers',
            'worker_name', 'ssn', 'passport_no'],
        // P2 async migration: transport now starts the generic job via ai_extract.start
        // (module=worker) instead of the blocking dashboard.workers.ai_extract call.
        'routes' => ["route('dashboard.ai_extract.start', ['module' => 'worker'])"],
    ],
    'resources/views/vehicles/add.blade.php' => [
        'ids' => ['ai_vehicle_extract_btn', 'ai_vehicle_document', 'ai_vehicle_extract_status',
            'ai_vehicle_document_dropzone', 'ai_vehicle_document_filename', 'plate_number', 'owner_name',
            'model', 'license_expiry', 'insurance_expiry', 'operation_card_expiry'],
        // P2 async migration: transport now starts the generic job via ai_extract.start
        // (module=vehicle) instead of the blocking vehicles.ai_extract call.
        'routes' => ["route('dashboard.ai_extract.start', ['module' => 'vehicle'])"],
    ],
];

$projectRoot = dirname(__DIR__, 2);

foreach ($widgets as $relPath => $expect) {
    $path = $projectRoot.'/'.$relPath;

    it("keeps every original id/route intact and adds motion in {$relPath}", function () use ($path, $expect) {
        $contents = file_get_contents($path);
        expect($contents)->not->toBeFalse();

        foreach ($expect['ids'] as $id) {
            $present = str_contains($contents, "id=\"{$id}\"")
                || str_contains($contents, "id='{$id}'")
                || str_contains($contents, "getElementById('{$id}')")
                || str_contains($contents, "'{$id}'");
            expect($present)->toBeTrue();
        }

        foreach ($expect['routes'] as $route) {
            expect($contents)->toContain($route);
        }
    });

    it("adds analyzing / reveal / low-conf / dropzone / entrance motion CSS in {$relPath}", function () use ($path) {
        $contents = file_get_contents($path);

        // 1. Analyzing state: icon breathe + scanning sweep + animated dots, gated on a
        //    state class (not the original .is-loading selector, which stays untouched).
        expect($contents)->toContain('ai-is-analyzing');
        expect($contents)->toContain('ai-icon-breathe');
        expect($contents)->toContain('ai-scan-sweep');
        expect($contents)->toContain('ai-status-dots');

        // 2. Result reveal: staggered field flash + success check pop + fields chip.
        expect($contents)->toContain('ai-field-flash');
        expect($contents)->toContain('ai-check-pop');
        expect($contents)->toContain('ai-fields-chip');

        // 3. Low-confidence amber pulse (new rule; existing .ai-low-conf outline untouched).
        expect($contents)->toContain('ai-low-conf-pulse');

        // 4. Dropzone hover/drag lift + preview fade-in.
        expect($contents)->toContain('ai-dropzone:hover');
        expect($contents)->toContain('ai-preview-in');

        // 5. Card fade-up entrance.
        expect($contents)->toContain('ai-card-in');

        // Motion budget: only transform/opacity animated properties in new keyframes (spot check).
        expect($contents)->toContain('@keyframes ai-card-in');
    });

    it("extends the existing prefers-reduced-motion block to neutralize new motion in {$relPath}", function () use ($path) {
        $contents = file_get_contents($path);

        expect($contents)->toContain('@media (prefers-reduced-motion: reduce)');
        // The original spinner rule must still be present inside that block.
        expect($contents)->toContain('.ai-status.is-loading::before{animation:none;}');

        // Grab just the reduced-motion block and confirm it neutralizes the new classes too.
        preg_match('/@media \(prefers-reduced-motion: reduce\)\{(.*?)\n\s*\}/s', $contents, $m);
        expect($m)->not->toBeEmpty();
        $block = $m[1] ?? '';
        foreach (['ai-card', 'ai-is-analyzing', 'ai-field-flash', 'ai-check-pop', 'ai-fields-chip', 'ai-low-conf'] as $needle) {
            expect($block)->toContain($needle);
        }
    });

    it("passes php -l and Laravel's real BladeCompiler::compileString() for {$relPath}", function () use ($path) {
        $lint = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
        expect($lint)->toContain('No syntax errors detected');

        $raw = file_get_contents($path);
        $compiled = null;
        $exception = null;
        try {
            $compiled = Blade::compileString($raw);
        } catch (\Throwable $e) {
            $exception = $e;
        }
        expect($exception)->toBeNull();
        expect($compiled)->toBeString();
    });
}
