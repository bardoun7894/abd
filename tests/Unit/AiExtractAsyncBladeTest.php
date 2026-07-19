<?php

// Guards Spec: async upload→poll pattern for the 4 module AI-extract forms.
// Each form must POST to the new generic start route with its module, poll the
// generic status route, keep the button disabled while running, cap polling at
// ~80 tries, and preserve every existing field-fill id inside applyExtraction().

uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Blade;

dataset('ai_extract_forms', [
    'workers' => [
        'path' => 'dashboard/workers/index.blade.php',
        'module' => 'worker',
        'oldRouteCall' => "route('dashboard.workers.ai_extract')",
        'fieldIds' => ['worker_name', 'ssn', 'passport_no', 'dob', 'doe', 'dop', 'nation_id'],
    ],
    'expense' => [
        'path' => 'dashboard/expense/expense_workall.blade.php',
        'module' => 'expense',
        'oldRouteCall' => "route('dashboard.expense.ai_extract')",
        'fieldIds' => ['expense_price', 'expense_respon', 'note', 'expense_month_desc', 'expense_categoty_id'],
    ],
    'manager' => [
        'path' => 'dashboard/manager/index.blade.php',
        'module' => 'manager',
        'oldRouteCall' => "route('dashboard.manager.ai_extract')",
        'fieldIds' => ['manager_name', 'manager_mobile'],
    ],
    'vehicles' => [
        'path' => 'vehicles/add.blade.php',
        'module' => 'vehicle',
        'oldRouteCall' => 'route("vehicles.ai_extract")',
        'fieldIds' => ['plate_number', 'owner_name', 'model', 'license_expiry', 'insurance_expiry', 'operation_card_expiry'],
    ],
]);

test('AI-extract form uses the async start→poll pattern, not the blocking sync call', function (string $path, string $module, string $oldRouteCall, array $fieldIds) {
    $full = resource_path("views/{$path}");
    expect(file_exists($full))->toBeTrue("Missing blade file: {$full}");

    $raw = file_get_contents($full);

    // 1) Starts the async job for this module via the new generic endpoint.
    expect($raw)->toContain("route('dashboard.ai_extract.start', ['module' => '{$module}'])");

    // 2) Polls the generic status endpoint.
    expect($raw)->toContain("url('dashboard/ai-extract/status')");

    // 3) Poll loop is capped (~80 tries) so it can't hang forever.
    expect($raw)->toContain('maxTries=80');

    // 4) The old blocking sync route call is gone entirely (not just re-wrapped) — it must
    // not appear anywhere in the file, since the async transport replaced it outright.
    expect($raw)->not->toContain($oldRouteCall);

    // 5) Field-fill logic (applyExtraction) is preserved — every original field id still set.
    expect($raw)->toContain('function applyExtraction(');
    foreach ($fieldIds as $id) {
        expect($raw)->toContain("'{$id}'");
    }

    // 6) Confidence key read from data.confidence (per spec: async data carries confidence).
    expect($raw)->toContain('.confidence');

    // 7) The whole file still compiles.
    $error = null;
    try {
        Blade::compileString($raw);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
    expect($error)->toBeNull("Blade compile failed for {$path}: {$error}");
})->with('ai_extract_forms');
