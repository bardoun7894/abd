# Feature Spec 004 — App-wide AI, embedded in the real screens

**Status:** Draft · **Depends on:** 001 (shared platform), 002 (Purchases AI), 003 (Rentals AI) · **Engine:** Google Gemini

## Overview
Extend AI across every module that benefits, **embedded inside the existing real screens and writing to the real tables** — not as a separate "الذكاء الاصطناعي" menu. Reuse the proven extraction platform (Gemini + staging + per-field confidence + review/approval + audit + versioning). The staging→approve→write-to-real-table pattern stays (so bad OCR never corrupts live data), but the **entry point and confirmation happen in the module's own screen**.

## Core principles
- **Real pages:** each module gets an "استخراج بالذكاء الاصطناعي" action inside its own existing screen (upload a document there, AI fills that screen's form, user confirms).
- **Real data:** approved extractions write to the module's real table (`purchase`, `expense`, `shop`, `workers`, `vehicles`, `moraslat`, `violation`), never a parallel schema.
- **Human-in-the-loop:** nothing is saved without user confirmation; per-field confidence shown; every action audited (`ai_audit_log`); reprocess keeps prior versions.

## Modules & requirements

### A. Embed the EXISTING invoice AI into real screens
- **A1 — المشتريات (purchase):** add an AI-upload button inside the purchase add/edit screen → OCR → prefill the purchase form (supplier, tax no., number, date, amounts, line items) → user confirms → saves to `purchase` (+ `purchase_items`). Replaces the separate "ترحيل" step with an in-screen flow.
- **A2 — الإيجارات (shop_rent):** connect the lease AI to the **existing** shop-rent module — extracted contracts fill `shop_rent` and generate `shop_rentpay` (not the parallel `lease_contracts`), OR migrate shop-rent onto the richer lease tables. (Design decision to confirm.)

### B. New document-OCR modules (reuse the pipeline)
- **B1 — المصاريف التشغيلية (expense):** OCR `expensefile` receipt → prefill amount / date / vendor / **auto-suggest `expense_categoty_id`** → save to `expense`. *(First to build — smallest.)*
- **B2 — المحلات (shop):** OCR السجل التجاري / رخصة البلدية / عقد الإيجار → extract CR no., license no., rent, **expiry dates** → save to `shop` + attachments → feed Home expiry alerts.
- **B3 — العمال (workers):** OCR Iqama / passport / ID → prefill `ssn`, `passport_no`, `registration_number`, `dob`, expiry → save to `workers`.
- **B4 — المركبات (vehicles):** OCR registration / insurance / license → extract `*_expiry` dates → save to vehicle record.

### C. New text / NLP AI
- **C1 — المعاملات (moraslat):** OCR scanned correspondence → **summarize** + auto-**classify** (type/category/status) + **draft Arabic reply** inside the moraslat screen.
- **C2 — المخالفات (violation):** draft Arabic violation-notice letters; classify violation side/severity.
- **C3 — التقارير (reports):** natural-language summaries / ask-your-data over the financial/expense/purchase aggregates.
- **C4 — الرئيسية (home):** AI monthly-insight summary card.

### Explicitly NOT AI (rule-based)
Home expiry alerts (date math + cron), payroll/financial/accountings arithmetic, constants/manager/emps/categories CRUD.

## Shared build
- Generalize the extractor into a document-type profile (schema + prompt + validators + target mapper) so each module supplies its own profile and target-table mapper. Reuse `GeminiClient`/`AbstractDocumentExtractor`, review/approval partial, `AuditLogger`, versioning, `AlertDispatcher`.
- Per module: a small `<Module>AiExtractor` profile + `<Module>Mapper` (staging → real table) + an in-screen upload/confirm UI partial.

## Verification
Each module: unit tests for its normalize/validate/mapper; end-to-end on the local Docker DB (real tables) + the MyContabo test server (real data) — upload a real document in the module's own screen → confirm → row written to the real table with audit + confidence.

## Build order (by value ÷ effort)
1. B1 Expense (smallest, reuses invoice engine) 2. A1 Purchase in-screen 3. B2 Shop 4. B3 Workers 5. B4 Vehicles 6. C1 Moraslat 7. C2 Violation 8. C3 Reports 9. A2 shop-rent bridge 10. C4 Home.
