# Spec 006 — AI Purchases + Rentals: compliance check vs. the professional requirements doc

Source: the client's detailed Arabic requirements (متطلبات تطوير نظام الذكاء الاصطناعي لوحدتي المشتريات والإيجارات).
This maps every requirement to the current implementation. Status: ✅ done · 🟡 partial · ⬜ missing · 🔒 blocked on credentials.

Live: http://91.230.110.187:9095 — model `gemini-flash-lite-latest`, async queue worker, encryption active.

---

## أولاً — وحدة المشتريات (Purchases)

| # | Requirement | Status | Where |
|---|---|---|---|
| 1 | OCR extract all fields (AR+EN, PDF/JPG/PNG) incl. line items, tax type, dates, currency, discounts, VAT %, payment method, notes | ✅ | `InvoiceExtractionService` (verified: batch #3 = 49 real invoices, 44 clean) |
| 2 | Auto-create purchase record + attach original + save extracted text + per-field confidence + processing status; draft pending approval | ✅ | `InvoicePurchaseMapper`, staging tables, `Invoice`/`InvoiceVersion` |
| 3 | Validation: tax-number format, no negatives, line-items vs total reconcile, VAT correct, supplier match + suggest | ✅ | `validate()` (15-digit + Saudi 3…3, total reconcile w/ tolerance), `SupplierMatcher::match` |
| 4 | Duplicate detection (invoice no / tax / supplier / date / amount / file fingerprint) → block + show original + reason + override-with-reason | ✅ | `DuplicateDetector::findDuplicate` |
| 5 | Review/approval screen (image, fields, confidence, unconfirmed, edit, re-extract, approve, reject, save-draft) | ✅ | `invoices/review.blade.php` |
| 6 | Error screen (reason, read %, error type, original file, reprocess, manual entry) | ✅ | `invoices/error.blade.php` |
| 7 | Reports dashboard (daily/monthly counts, totals, VAT, duplicates, rejected, needs-review, avg time, AI success %, top suppliers, top items, interactive charts) | ✅ | `invoices/report.blade.php` (verified live) |

## ثانياً — وحدة الإيجارات (Rentals)

| # | Requirement | Status | Where |
|---|---|---|---|
| 1 | Lease OCR (all contract + property + payment fields, deposit, renewal/cancel/increase terms) | ✅ | `LeaseExtractionService` (verified on EJAR contract) |
| 2 | Auto-create contract + attach + extracted text + confidence + await approval | ✅ | `LeaseContract`, staging |
| 3 | Auto payment schedule (no, due date, value, status, paid date, remaining, penalties) | ✅ | `LeaseScheduleGenerator`, `LeasePayment` |
| 4 | Smart alerts: 10/5/0 days before due, overdue, 30/15/0 before expiry, on renewal, late payments — in-app + email + SMS | ✅ | `LeaseScanAlerts` command + `AlertDispatcher` (SMS 🔒 creds) |
| 5 | Analytics: collection %, monthly/annual revenue, ended/active/renewable/troubled, top tenants, avg payment period | ✅ | `leases/analytics.blade.php` |
| 5b | **Revenue forecast + AI collection-trend analysis (التوقعات + تحليل الاتجاهات بالذكاء)** | ⬜ **GAP** | not built |
| 6 | Unprocessed-contracts screen (original, read %, failure reasons, unread fields, edit, re-run AI, approve after edit) | ✅ | `leases/unprocessed.blade.php` |

## ثالثاً — عام (General / governance)

| # | Requirement | Status |
|---|---|---|
| G1 | No auto-approve; everything reviewable before final save | ✅ staging → approve |
| G2 | Audit log (read/edit/approve/reject/reprocess + user + datetime) | ✅ `AuditLogger` |
| G3 | AI performance metrics (success %, error %, docs processed) + reprocess anytime + keep all versions (no delete) | ✅ `InvoiceVersion`, report |
| G4 | File encryption + permissions + backups + full log | ✅ encryption (T-C1), `Perm`; backups = ops |
| G5 | Future integration: ZATCA, email, SMS, file storage | 🟡 ZATCA Ph1 QR ✅ / Ph2 🔒; email ✅; SMS 🔒 creds; storage ✅ |

---

## GAP TASKS (this spec)

### T6-1 — In-screen per-field confidence display (UI) ⬜ P0
Review screen shows confidence; the **in-screen widgets do not** (0/7). After prefill, outline low-confidence fields amber + "⚠ راجع". Additive JS only. Spec ref: نسبة الثقة لكل حقل.

### T6-2 — In-screen document preview (UI) ⬜ P0
6/7 widgets have no preview of the uploaded file. Show a thumbnail next to the prefilled fields so the user can visually verify. (Vehicles already has it — reuse.)

### T6-3 — Rentals revenue forecast + AI collection-trend (analytics) ⬜ P1
Add to `leases/analytics`: (a) next-N-months revenue projection from the payment schedule + historical collection rate, (b) an AI narrative trend analysis (reuse `ReportsNlService`/Gemini) over collection history. New `LeaseForecastService`.

### T6-4 — Re-analyze button + extraction summary toast (UI) 🟡 P1
One-click re-run in each widget + a "تم استخراج N حقول" summary after prefill.

### T6-5 — Mobile responsiveness + a11y pass (UI) 🟡 P2
`table-responsive` on invoice/lease tables; labels/ARIA on file inputs + drop-zones; RTL contrast.

### T6-6 — SMS activation 🔒 (creds: Taqnyat key + sender)
### T6-7 — ZATCA Phase 2 clearance 🔒 (creds: CR + OTP → CSID)
### T6-8 — FCM push channel (تطبيقات الإشعارات) ⬜ P2

## Order: T6-1, T6-2 (P0 UI) → T6-3 (forecast) → T6-4/T6-5 → creds-blocked.
