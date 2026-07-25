<?php

use App\Http\Controllers\Dashboard\InvoiceController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

/**
 * Spec 024 F1 follow-up — the two gaps the first F1 pass left:
 *
 *  1. Only the per-invoice push stamped "الفرع المُرحّل إليه"/تاريخ/الموظف and
 *     wrote a TRANSFER audit row. An invoice posted through the batch-level
 *     paths (pushToPurchase / bulkPush) landed with an EMPTY branch cell and no
 *     per-invoice audit trail. stampTransferred() is now the single choke point
 *     all three paths funnel through.
 *  2. There was no "إرجاع" at all, so the client's "منع ترحيل نفس الفاتورة أكثر
 *     من مرة إلا بعد إرجاعها" had no إرجاع half. returnInvoice() reverses the
 *     push properly — voids the سند, deletes the purchase — and leaves the
 *     invoice transferable again.
 *
 * Same two-independent-:memory:-sqlite-DB pattern as InvoiceRerouteTest.
 */
beforeEach(function () {
    Invoice::flushEventListeners();

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    config()->set('database.connections.invoices.driver', 'sqlite');
    config()->set('database.connections.invoices.database', ':memory:');
    DB::purge('invoices');

    Schema::create('purchase', function ($t) {
        $t->id('purchase_id');
        $t->string('purchase_no')->nullable();
        $t->unsignedBigInteger('shop_id')->nullable();
        $t->unsignedBigInteger('manager_id')->nullable();
        $t->timestamp('created_at')->nullable();
    });

    Schema::create('purchase_items', function ($t) {
        $t->id();
        $t->unsignedBigInteger('purchase_id');
        $t->string('name')->nullable();
    });

    Schema::create('purchase_attach', function ($t) {
        $t->id('purchase_attach_id');
        $t->unsignedBigInteger('purchase_id');
        $t->string('attach_url')->nullable();
    });

    Schema::create('cash_receipt', function ($t) {
        $t->id('receipt_id');
        $t->string('receipt_no', 30)->nullable()->unique();
        $t->string('source_type', 20);
        $t->unsignedBigInteger('source_id');
        $t->string('direction', 3);
        $t->decimal('amount', 14, 2);
        $t->date('receipt_date');
        $t->string('payer_name')->nullable();
        $t->unsignedBigInteger('received_by')->nullable();
        $t->text('note')->nullable();
        $t->tinyInteger('is_void')->default(0);
        $t->text('void_reason')->nullable();
        $t->unsignedBigInteger('void_user')->nullable();
        $t->dateTime('void_date')->nullable();
        $t->unsignedBigInteger('create_user')->nullable();
        $t->dateTime('created_at')->nullable();
    });

    Schema::create('cashbox_ledger', function ($t) {
        $t->id('entry_id');
        $t->unsignedBigInteger('receipt_id')->nullable();
        $t->string('source_type', 20);
        $t->unsignedBigInteger('source_id');
        $t->string('direction', 3);
        $t->decimal('amount', 14, 2);
        $t->decimal('balance_after', 14, 2)->nullable();
        $t->unsignedBigInteger('reversal_of_entry_id')->nullable();
        $t->unsignedBigInteger('change_user')->nullable();
        $t->dateTime('change_at')->nullable();
        $t->text('note')->nullable();
    });

    Schema::create('ai_audit_log', function ($t) {
        $t->id();
        $t->string('document_type', 20)->index();
        $t->unsignedBigInteger('document_id')->nullable()->index();
        $t->unsignedBigInteger('batch_id')->nullable();
        $t->string('action', 30)->index();
        $t->string('field')->nullable();
        $t->text('old_value')->nullable();
        $t->text('new_value')->nullable();
        $t->unsignedBigInteger('change_user')->nullable()->index();
        $t->dateTime('change_at')->nullable();
        $t->text('note')->nullable();
    });

    Schema::create('shop', function ($t) {
        $t->id('shop_id');
        $t->string('shop_name')->nullable();
    });

    Schema::create('manager', function ($t) {
        $t->id('manager_id');
        $t->string('manager_name')->nullable();
    });

    Schema::create('permission', function ($t) {
        $t->id();
        $t->unsignedBigInteger('emp_id')->nullable();
        $t->unsignedBigInteger('function_id')->nullable();
    });

    require_once base_path('database/migrations/invoices/2026_06_16_000001_create_invoice_batches_table.php');
    require_once base_path('database/migrations/invoices/2026_06_16_000002_create_invoices_table.php');
    require_once base_path('database/migrations/invoices/2026_06_17_000005_add_purchase_mapping_to_invoices.php');
    require_once base_path('database/migrations/invoices/2026_07_24_000016_add_transfer_fields_to_invoices.php');

    (new CreateInvoiceBatchesTable())->up();
    (new CreateInvoicesTable())->up();
    (new AddPurchaseMappingToInvoices())->up();
    (new AddTransferFieldsToInvoices())->up();

    DB::table('shop')->insert(['shop_id' => 10, 'shop_name' => 'فرع الرياض']);
    DB::table('shop')->insert(['shop_id' => 20, 'shop_name' => 'فرع جدة']);
});

afterEach(function () {
    Invoice::flushEventListeners();
    Mockery::close();
});

function returnActingAs(int $id, int $empJob): void
{
    Auth::shouldReceive('user')->andReturn((object) ['id' => $id, 'emp_job' => $empJob, 'emp_name' => 'T']);
    Auth::shouldReceive('id')->andReturn($id);
    Auth::shouldReceive('check')->andReturn(true);
}

/**
 * A fully posted invoice: purchase row + line item + attachment + the سند صرف
 * push() creates, mirroring the real post-push end state.
 *
 * @return array{0:int,1:int,2:int} [invoiceId, purchaseId, batchId]
 */
function seedFullyPostedInvoice(int $userId, int $shopId, string $branchLabel = 'فرع الرياض'): array
{
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => $userId, 'status' => 'done', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchase')->insertGetId([
        'purchase_no' => 'PN-'.uniqid(), 'shop_id' => $shopId, 'created_at' => now(),
    ]);
    DB::table('purchase_items')->insert(['purchase_id' => $purchaseId, 'name' => 'صنف']);
    DB::table('purchase_attach')->insert(['purchase_id' => $purchaseId, 'attach_url' => '/x.jpg']);

    $receiptId = DB::table('cash_receipt')->insertGetId([
        'receipt_no' => 'R-'.uniqid(), 'source_type' => 'purchase', 'source_id' => $purchaseId,
        'direction' => 'out', 'amount' => 100, 'receipt_date' => '2026-07-01',
        'is_void' => 0, 'created_at' => now(),
    ], 'receipt_id');
    DB::table('cashbox_ledger')->insert([
        'receipt_id' => $receiptId, 'source_type' => 'purchase', 'source_id' => $purchaseId,
        'direction' => 'out', 'amount' => 100, 'balance_after' => -100, 'change_at' => now(),
    ]);

    $invId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 1, 'invoice_number' => 'IN-'.uniqid(),
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'purchase_id' => $purchaseId, 'mapped_at' => now(),
        'transferred_branch_label' => $branchLabel, 'transferred_at' => now(), 'transferred_by' => $userId,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$invId, $purchaseId, $batchId];
}

// ---------------------------------------------------------------------------
// إرجاع الفاتورة
// ---------------------------------------------------------------------------

it('returns a posted invoice: voids its سند, deletes the purchase, clears the transfer fields', function () {
    returnActingAs(1, 1);
    [$invId, $purchaseId] = seedFullyPostedInvoice(1, 10);

    $response = (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'رُحّلت للفرع الخطأ']), $invId
    );

    expect($response->getData(true)['status'])->toBeTrue();

    // The سند is voided, never deleted, and a compensating entry was appended.
    $receipt = DB::table('cash_receipt')->where('source_id', $purchaseId)->first();
    expect((int) $receipt->is_void)->toBe(1);
    expect($receipt->void_reason)->toContain('رُحّلت للفرع الخطأ');
    expect(DB::table('cashbox_ledger')->where('direction', 'in')->count())->toBe(1);

    // The purchase and everything hanging off it are gone.
    expect(DB::table('purchase')->where('purchase_id', $purchaseId)->exists())->toBeFalse();
    expect(DB::table('purchase_items')->where('purchase_id', $purchaseId)->exists())->toBeFalse();
    expect(DB::table('purchase_attach')->where('purchase_id', $purchaseId)->exists())->toBeFalse();

    // The invoice is unlinked and transferable again.
    $inv = Invoice::find($invId);
    expect($inv->purchase_id)->toBeNull();
    expect($inv->transferred_branch_label)->toBeNull();
    expect($inv->transferred_at)->toBeNull();
    expect($inv->transferred_by)->toBeNull();
});

it('audits the إرجاع with the branch it came from and the reason', function () {
    returnActingAs(7, 1);
    [$invId] = seedFullyPostedInvoice(7, 10, 'فرع الرياض');

    (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'خطأ إدخال']), $invId
    );

    $audit = DB::table('ai_audit_log')->where('action', 'returned')->first();
    expect($audit)->not->toBeNull();
    expect((int) $audit->document_id)->toBe($invId);
    expect($audit->old_value)->toBe('فرع الرياض');
    expect($audit->new_value)->toBeNull();
    expect($audit->note)->toContain('خطأ إدخال');
    expect((int) $audit->change_user)->toBe(7);
});

it('refuses إرجاع without a reason (422) and changes nothing', function () {
    returnActingAs(1, 1);
    [$invId, $purchaseId] = seedFullyPostedInvoice(1, 10);

    $response = (new InvoiceController())->returnInvoice(Request::create('/', 'POST', []), $invId);

    expect($response->getStatusCode())->toBe(422);
    expect(DB::table('purchase')->where('purchase_id', $purchaseId)->exists())->toBeTrue();
    expect((int) DB::table('cash_receipt')->where('source_id', $purchaseId)->value('is_void'))->toBe(0);
});

it('refuses إرجاع for a non-admin without the special permission (403)', function () {
    returnActingAs(42, 0);
    [$invId, $purchaseId] = seedFullyPostedInvoice(42, 10);

    $response = (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'محاولة']), $invId
    );

    expect($response->getStatusCode())->toBe(403);
    expect(DB::table('purchase')->where('purchase_id', $purchaseId)->exists())->toBeTrue();
});

it('allows إرجاع for a non-admin holding the return permission', function () {
    returnActingAs(42, 0);
    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => InvoiceController::RETURN_FUNCTION_ID]);
    [$invId, $purchaseId] = seedFullyPostedInvoice(42, 10);

    $response = (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'تصحيح']), $invId
    );

    expect($response->getStatusCode())->toBe(200);
    expect(DB::table('purchase')->where('purchase_id', $purchaseId)->exists())->toBeFalse();
});

it('does NOT let the re-route permission alone authorise an إرجاع', function () {
    // The whole point of splitting 222/223: re-routing moves a purchase, returning
    // DELETES it. Someone trusted to move an invoice between branches is not
    // thereby trusted to erase the purchase and void its سند.
    returnActingAs(42, 0);
    DB::table('permission')->insert(['emp_id' => 42, 'function_id' => InvoiceController::REROUTE_FUNCTION_ID]);
    [$invId, $purchaseId] = seedFullyPostedInvoice(42, 10);

    $response = (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'محاولة']), $invId
    );

    expect($response->getStatusCode())->toBe(403);
    expect(DB::table('purchase')->where('purchase_id', $purchaseId)->exists())->toBeTrue();
    expect((int) DB::table('cash_receipt')->where('source_id', $purchaseId)->value('is_void'))->toBe(0);
    expect(InvoiceController::RETURN_FUNCTION_ID)->not->toBe(InvoiceController::REROUTE_FUNCTION_ID);
});

it('refuses إرجاع for an invoice that was never transferred (422)', function () {
    returnActingAs(1, 1);
    $batchId = DB::connection('invoices')->table('invoice_batches')->insertGetId([
        'user_id' => 1, 'status' => 'done', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $invId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 1, 'invoice_number' => 'NP-9',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 100, 'status' => 'done',
        'needs_review' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $response = (new InvoiceController())->returnInvoice(
        Request::create('/', 'POST', ['reason' => 'x']), $invId
    );

    expect($response->getStatusCode())->toBe(422);
});

// ---------------------------------------------------------------------------
// The single stamping choke point — reachable from every push path
// ---------------------------------------------------------------------------

it('exposes ONE stamping path that every push route funnels through', function () {
    $src = file_get_contents(base_path('app/Http/Controllers/Dashboard/InvoiceController.php'));

    // Three call sites: pushInvoices (per-invoice), pushToPurchase (one batch),
    // bulkPush (many batches). The regression was that only the first stamped.
    expect(substr_count($src, '$this->stampTransferred('))->toBe(3);

    // And the audit row carries the branch in new_value, not only in the note,
    // so the log stays queryable by branch.
    expect($src)->toContain("'new' => \$targetLabel,");
});

it('stamps the branch/date/employee and audits when a batch-level push posts an invoice', function () {
    returnActingAs(5, 1);

    // An invoice already posted BEFORE this call (must not be re-stamped) plus a
    // freshly posted one (must be stamped) — the exact distinction stampTransferred
    // exists to make.
    [$oldInvId] = seedFullyPostedInvoice(5, 10, 'فرع قديم');
    $batchId = Invoice::find($oldInvId)->batch_id;

    $purchaseId = DB::table('purchase')->insertGetId([
        'purchase_no' => 'PN-new', 'shop_id' => 20, 'created_at' => now(),
    ]);
    $newInvId = DB::connection('invoices')->table('invoices')->insertGetId([
        'batch_id' => $batchId, 'page_number' => 2, 'invoice_number' => 'IN-new',
        'invoice_date' => '2026-07-01', 'total_incl_vat' => 50, 'status' => 'done',
        'needs_review' => 0, 'purchase_id' => $purchaseId, 'mapped_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $controller = new InvoiceController();
    $stamp = (new ReflectionClass($controller))->getMethod('stampTransferred');
    $stamp->setAccessible(true);
    $stamped = $stamp->invoke($controller, [$oldInvId, $newInvId], [$oldInvId], 'فرع جدة', 'ترحيل دفعة');

    expect($stamped)->toBe(1);

    $new = Invoice::find($newInvId);
    expect($new->transferred_branch_label)->toBe('فرع جدة');
    expect($new->transferred_at)->not->toBeNull();
    expect($new->transferred_by)->toBe(5);

    // The already-posted one keeps its original label — no forged transfer.
    expect(Invoice::find($oldInvId)->transferred_branch_label)->toBe('فرع قديم');

    $audit = DB::table('ai_audit_log')->where('action', 'transfer')->get();
    expect($audit)->toHaveCount(1);
    expect((int) $audit->first()->document_id)->toBe($newInvId);
    expect($audit->first()->new_value)->toBe('فرع جدة');
    expect($audit->first()->old_value)->toBeNull();
});

it('records the previous branch in old_value when re-stamping an already-labelled invoice', function () {
    returnActingAs(5, 1);
    [$invId] = seedFullyPostedInvoice(5, 10, 'فرع الرياض');

    $controller = new InvoiceController();
    $stamp = (new ReflectionClass($controller))->getMethod('stampTransferred');
    $stamp->setAccessible(true);
    $stamp->invoke($controller, [$invId], [], 'فرع جدة', 'ترحيل');

    $audit = DB::table('ai_audit_log')->where('action', 'transfer')->first();
    expect($audit->old_value)->toBe('فرع الرياض');
    expect($audit->new_value)->toBe('فرع جدة');
});

it('still posts (and audits) on a schema that predates the F1 transfer columns', function () {
    // The regression this guards: branchLabel()/stampTransferred() are now on the
    // hot path of EVERY push. If either hard-required the F1 schema, an
    // environment that had not run 2026_07_24_000016 would fail the ترحيل
    // outright instead of merely losing the denormalized label.
    returnActingAs(5, 1);
    [$invId] = seedFullyPostedInvoice(5, 10, 'فرع الرياض');

    Schema::connection('invoices')->table('invoices', function ($t) {
        $t->dropColumn(['transferred_branch_label', 'transferred_at', 'transferred_by']);
    });
    Schema::drop('shop');

    $controller = new InvoiceController();
    $stamp = (new ReflectionClass($controller))->getMethod('stampTransferred');
    $stamp->setAccessible(true);

    $label = (new ReflectionClass($controller))->getMethod('branchLabel');
    $label->setAccessible(true);
    expect($label->invoke($controller, 20, null))->toBe('محل #20'); // degrades, never throws

    expect($stamp->invoke($controller, [$invId], [], 'فرع جدة', 'ترحيل'))->toBe(1);
    expect(DB::table('ai_audit_log')->where('action', 'transfer')->count())->toBe(1);
});

it('wires the إرجاع route and its UI button behind its own flag', function () {
    expect(file_get_contents(base_path('routes/dashboard.php')))
        ->toContain("'returnInvoice'");

    $blade = file_get_contents(base_path('resources/views/dashboard/invoices/show.blade.php'));
    expect($blade)->toContain('js-inv-return');
    expect($blade)->toContain("'/return'");
    // The button must follow $canReturn, not $canReroute.
    expect($blade)->toContain('@if($canReturn)');
    expect($blade)->toContain('if (canReturn && v.purchase_id)');
});

it('keeps the Excel palette identical to the CSS brand tokens', function () {
    // Exports used to ship 1B8A5A — a lighter, unrelated green — so every
    // workbook looked off-brand next to the screen it came from.
    expect(\App\Services\ExcelReportStyler::EMERALD)->toBe('0E6B4F');
    expect(\App\Services\ExcelReportStyler::EMERALD_DEEP)->toBe('0A4F3A');
    expect(\App\Services\ExcelReportStyler::ZEBRA)->toBe('E4EFE9');

    $css = file_get_contents(base_path('public/css/app-ui.css'));
    expect($css)->toContain('--sn-emerald: #0e6b4f');
    expect($css)->toContain('--sn-emerald-deep: #0a4f3a');
    expect($css)->toContain('--sn-emerald-tint: #e4efe9');

    // And no call site may re-declare the hexes (the old drift vector).
    $controller = file_get_contents(base_path('app/Http/Controllers/Dashboard/InvoiceController.php'));
    expect($controller)->not->toContain("'1B8A5A'");
    expect($controller)->not->toContain("'116149'");
    expect($controller)->not->toContain("'D7EEE3'");   // off-brand mint on the totals row
    expect($controller)->toContain('ExcelReportStyler::EMERALD');
});

it('leaves no off-brand fill hex in any live Excel export', function () {
    // ReportController@@.php is a dead, unrouted backup (cyan 33F0FF headers) and
    // is excluded on purpose — nothing references it.
    $live = [
        'app/Http/Controllers/Dashboard/InvoiceController.php',
        'app/Http/Controllers/Dashboard/ReportController.php',
        'app/Http/Controllers/Dashboard/WorkersController.php',
        'app/Http/Controllers/TaskController.php',
        'app/Services/ExcelReportStyler.php',
    ];
    $allowed = ['0E6B4F', '0A4F3A', 'E4EFE9', 'CBD5D1', 'FFFFFFFF', 'FF000000'];

    $offBrand = [];
    foreach ($live as $rel) {
        $path = base_path($rel);
        if (! file_exists($path)) {
            continue;
        }
        preg_match_all("/setARGB\(\s*'([0-9A-Fa-f]{6,8})'\s*\)/", file_get_contents($path), $m);
        foreach ($m[1] as $hex) {
            if (! in_array(strtoupper($hex), $allowed, true)) {
                $offBrand[] = "{$rel}: {$hex}";
            }
        }
    }

    expect($offBrand)->toBe([]);
});
