# Plan — Invoice "fix center" + AI reliability fixes & roadmap (Spec 013)

## Context
Two client asks:
1. **See + fix the invoices that didn't post because of issues.** Bulk ترحيل skips invoices that are `needs_review` or missing رقم/تاريخ/إجمالي (they show as "جزئياً X/Y" or "غير مُرحّلة"). Today the only way to fix them is to open each batch (`عرض`) one at a time. The client wants a direct place to fix them, then re-ترحيل.
2. **Complete the app's AI — advice + implement the top fixes.** A KB + code audit found the Gemini *engine* is mature (retry, cache, dedup, concurrency, escalation, validation, versioning) but the **operational edges** are the gaps.

Deploy target: MyContabo (server), same manual git-pull flow.

---

## Part A — Invoice "مركز التصحيح" (fix center) + clickable badge  [BUILD]

Reuse (do NOT rebuild): `InvoicePurchaseMapper::ineligibilityReason()` (the reason source), `_edit_modal.blade.php` + the `correct` endpoint (`InvoiceController::correct` — clears `needs_review`), the bulk push flow.

1. **New cross-batch view** — route `GET /invoices/needs-fix` → `InvoiceController::needsFix()`. Lists every owned invoice that is **un-posted AND blocked**: `purchase_id IS NULL` AND (`needs_review=1` OR missing `invoice_number`/`invoice_date`/`total_incl_vat`) — across all batches (non-admin `user_id` scoped, mirroring `error()` at InvoiceController.php:449). Optional `?batch_id=` filter. Columns: batch/file, رقم الفاتورة, التاريخ, الإجمالي, **السبب** (`ineligibilityReason`), + a **تعديل** button per row (reuse `_edit_modal` + `correct`). Paginated. New view `dashboard/invoices/needs_fix.blade.php`.
2. **Fix → re-post loop** — after `correct` clears the block, the row drops out of the list (re-poll/refresh). Add a **"ترحيل المُصحّحة"** button that calls the existing bulk push for the affected batches (already skips already-posted → no duplicates). Sidebar/link entry to the page.
3. **Clickable badge** — in `index.blade.php`, make the "جزئياً X/Y" / "غير مُرحّلة" badge a link to `/invoices/needs-fix?batch_id={{ $b->id }}` (fully-مُرحّلة batches keep the plain green badge, no link).

## Part B — AI reliability fixes  [IMPLEMENT NOW — top 4, low-risk/high-impact]

1. **Batch stale-job recovery (P0).** `RecoverStaleAiExtractionJobs.php:41` only sweeps interactive `AiExtractionJob`; `InvoiceBatch`/`LeaseBatch` stuck in `status='processing'` (worker OOM/deploy/sync-fatal) wedge forever with the polling UI never resolving. Extend the scheduled sweeper to detect batches processing past a threshold and mark them `failed` (or re-queue), so they stop spinning. Reuse the existing command + schedule.
2. **`PurchaseController::aiExtract` sync-freeze (P0).** Line 381 calls `extractInvoice()` with the full 120s×4-retry budget inside a live web request — can freeze a PHP-FPM worker for minutes. Route it through the interactive fast-fail path / `extractAdaptive` like the other interactive extractors (~25-40s cap).
3. **`GeminiClient` ConnectionException retry (KB P0).** The retry loop keys on 429/5xx; cURL 28/56 (`gemini-3.5-flash` overload) throws a `ConnectionException` that bypasses retry — hard docs under load get zero retries. Add ConnectionException to the retriable path with backoff (GeminiClient.php retry block).
4. **AI dashboard: errors + per-user (P2/observability).** `SettingsController::aiUsage` shows cost/tokens/cache-hit by module/day but **no failure count, no 429 rate, no per-user spend** — though `ai_usage_log` already logs `user_id` and outcomes. Add those columns/cards (read-only reporting; also partially delivers the never-built FR-006 audit view).

## Part B — AI roadmap  [ADVICE — build later, prioritized]
- **P0:** admin alerting — wire `AlertDispatcher` into `GeminiClient`/jobs (429 storms, quota-exhaustion currently `Log::error` only).
- **P1:** atomic reprocess guard (read-then-write race); per-page resume (retry re-bills completed pages); confidence-driven `needs_review` in the batch path (batch ignores stored `confidence`/`field_confidence` that the interactive path uses); auto re-queue of deadline-truncated pages (currently manual "re-read missing").
- **P1 (larger):** batch **lease** pipeline redesign — page ≠ contract (EJAR 8-page → 7/8 "need review"); per-user AI budget/quota (gate is one global row); human-correction feedback loop (versions stored, never fed back as few-shot/per-supplier hints).
- **P2:** circuit breaker on a failing model/key; ZATCA-QR **decode** of incoming invoices to cross-check extraction (codec already exists in `ZatcaQrGenerator::tlv()`); `generateText()` engine-level cache (`ReportsNlService` uncached); robust invoice-count heuristic.

---

## Execution
Branch `013-invoice-fix-center-ai` off `main`. Build with a **Workflow** (Opus plan → Sonnet 5 execute), sequential where files overlap (InvoiceController + index.blade + routes are shared by Part A; Part B files are mostly disjoint — GeminiClient, RecoverStaleAiExtractionJobs, PurchaseController, SettingsController). No commit/deploy inside the workflow; I review the diff + tests, then deploy to MyContabo and verify.

## Verification
- **Pest:** `needsFix()` query (blocked-only, ownership-scoped); stale-batch sweeper marks an old `processing` batch failed and leaves fresh ones; ConnectionException triggers a retry; aiUsage dashboard aggregates failures/per-user.
- **Manual/prod:** click a "جزئياً" badge → fix-center opens filtered to that batch → تعديل an invoice → it leaves the list → ترحيل المُصحّحة posts it (no duplicates); AI dashboard shows failure + per-user columns; force a stuck batch and confirm the sweeper clears it.

## Critical files
- `app/Http/Controllers/Dashboard/InvoiceController.php` (needsFix + bulk-push reuse), `routes/dashboard.php`, `resources/views/dashboard/invoices/{index,needs_fix}.blade.php`, `_edit_modal.blade.php` (reuse)
- `app/Console/Commands/RecoverStaleAiExtractionJobs.php` + Kernel schedule, `app/Services/GeminiClient.php`, `app/Http/Controllers/Dashboard/PurchaseController.php`, `app/Http/Controllers/Dashboard/SettingsController.php` (aiUsage) + its view
