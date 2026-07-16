# Feature Spec 001 — Shared AI Document Platform (منصة استخراج المستندات)

**Status:** Draft · **Depends on:** existing Gemini invoice extraction · **Blocks:** 002, 003

## Overview
A shared foundation that both the Purchases-AI (002) and Rentals-AI (003) modules build on. Generalizes the existing Gemini invoice pipeline into a reusable document-extraction platform with per-field confidence, human-in-the-loop review/approval, versioned reprocessing, a full audit log, and a multi-channel alert dispatcher (in-app + email + SMS). Keeps Google Gemini as the extraction engine.

## User scenarios
- As a data-entry user, I upload a document (PDF/JPG/PNG) and the system extracts structured fields with a confidence score per field, so I can trust high-confidence fields and quickly fix low-confidence ones.
- As an approver, no record is ever committed automatically — I review, edit, then approve/reject/save-as-draft.
- As an auditor, every read/edit/approve/reject/reprocess is logged with the actor and timestamp, and no prior version is ever deleted.
- As an operator, I receive due/expiry alerts in the app bell, by email, and by SMS.

## Functional requirements
- **FR-001** Reuse the Gemini call (retry/backoff/cost) and rasterization/2-pass pipeline; behavior for existing invoices must remain identical (regression-tested) after refactor into `GeminiClient` + `AbstractDocumentExtractor`.
- **FR-002** Extraction returns a confidence score **per field** (not just per document); stored as `field_confidence` JSON.
- **FR-003** Every document has a status machine: `draft → pending_review → approved | rejected | failed | reprocessing`. No record reaches `approved` without an explicit authorized-user action.
- **FR-004** Reprocessing creates a **new version** of the extraction; prior versions are retained (`version`, `superseded_by`), never deleted.
- **FR-005** A reusable review screen shows: source image, extracted fields, per-field confidence, highlighted unconfirmed fields, and actions edit / re-extract / approve / reject / save-draft. A reusable error screen shows failure reason, read %, error type, original file, reprocess, manual entry.
- **FR-006** An `AuditLogger` writes to `ai_audit_log` (document_type, document_id, action, field, old/new value, change_user, change_at) on every read/edit/approve/reject/reprocess/duplicate-override; display joins `users` on `change_user`.
- **FR-007** An `AlertDispatcher` sends via three drivers — in-app (`app_notifications` surfaced in the existing bell), email (Mailable over configured SMTP), SMS (`SmsClient`, Saudi provider). Channels are configurable per alert.
- **FR-008** New screens are access-controlled via `Perm` (new `per_controller`/`per_function`/`role_per`/`permission` rows) and appear in the sidebar gated by `Perm::get_controll_access/get_function_access`.
- **FR-009** AI performance metrics available: extraction success rate, error rate, documents processed, average processing time.

## Key entities
- `ai_audit_log`, `app_notifications` (main DB); extraction `version`/`superseded_by`/`field_confidence` columns (staging DB).
- Services: `GeminiClient`, `AbstractDocumentExtractor`, `AlertDispatcher`, `SmsClient`, `AuditLogger`.

## Out of scope
- ZATCA live integration (documented seam only). Provider credentials for SMS (client-supplied).

## Reuse map
`InvoiceExtractionService::callGemini/costUsd`, `InvoicePipeline`, `PdfPageRasterizer`; `Perm`; `moraslat` bell (`HomeController::notify_num/load_alerts`); `*_history` insert idiom (`MoraslatController.php:452`); SMTP in `.env`.
