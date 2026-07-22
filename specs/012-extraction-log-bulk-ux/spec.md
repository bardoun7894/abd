# Spec 012 — Extraction-log bulk UX (filters + multi-select + bulk ترحيل/export)

**Target page:** `resources/views/dashboard/invoices/index.blade.php` (سجل عمليات الاستخراج) + `InvoiceController` + `routes/dashboard.php`.

## Context
The extraction-log list shows batches (file, #invoices, total, status, date). Today the only actions are per-batch (open → pick shop → post). Client wants: filter the list, multi-select batches, and act in bulk (post to a shop / export). Posting itself works (`InvoicePurchaseMapper::push` posts a whole batch to a shop XOR manager).

## A — Filters
Extend `InvoiceController::index()` query + add a filter bar to `index.blade`:
- search `q` (exists — `original_filename` like), status (exists), **date_from/date_to** (`created_at`), **min invoice count** (`processed_pages >= n`).
- Apply the SAME filters to `exportBatches()` so export respects them (already reads q/status; add date + min_count).

## B — Multi-select + bulk-action UI
- Checkbox column + a select-all header checkbox on the batch table.
- A bulk-action bar (visible when ≥1 selected): **"ترحيل المحدد"** (opens a shop/manager picker modal, reuse `Shop::get()` + `get_manager()` — pass to `index()` view) and **"تصدير المحدد"** (export only selected batch ids).
- JS collects selected batch ids.

## C — Bulk backend + routes
- `bulkPush(Request)`: validate `batch_ids[]` + shop_id XOR manager_id; for each owned batch call `InvoicePurchaseMapper::push`; aggregate a combined summary (pushed/ineligible/duplicates/errors, per-batch); `Log::info`. Route `POST /invoices/bulk-push` (JSON action — guarded by ai_access like `pushToPurchase`, NOT a WEB_METHOD).
- Extend `exportBatches()` to accept optional `batch_ids[]` (export only selected; else all-with-filters). Keep it a WEB_METHOD.
- Enforce non-admin `user_id` ownership on every batch (mirror `index()` scoping + `findOwned`).

## Reuse
- `InvoicePurchaseMapper::push` (whole-batch post), `Shop::get()`/`get_manager()`, existing `exportBatches` professional styling, `dashboard.invoices.push` pattern, `filters` array pattern in `index()`.

## Tests / verification
- Feature/unit: `index()` date+count filters; `bulkPush` aggregates + ownership + shop-XOR-manager; `exportBatches` honors `batch_ids[]`.
- Routes registered; php -l; deploy to MyContabo (no migration — pure code/view).
