# Feature Spec 002 — Purchases AI (وحدة المشتريات)

**Status:** Draft · **Depends on:** 001 (shared platform) · **Engine:** Google Gemini

## Overview
Extend the existing AI invoice extraction into a full purchases system: read every purchase invoice (PDF/JPG/PNG, Arabic + English), extract full data **including line items**, auto-create a draft purchase record pending approval, validate + match suppliers, detect duplicates, and provide review / error / duplicate screens plus a reports dashboard.

## User scenarios
- Upload an invoice → system OCR-extracts header + line items with per-field confidence → draft purchase created → user reviews/edits → approves → final purchase record + line items written.
- On a suspected duplicate, the system blocks creation, shows the original and the reason, and allows an override with a logged reason.
- A manager views a dashboard of daily/monthly counts, totals, VAT, duplicates, rejections, AI success rate, top suppliers, top items.

## Functional requirements
- **FR-101** Extract (Arabic+English): supplier name, issuing-entity name, supplier tax number, commercial-registration number, invoice number, **invoice type (ضريبية / ضريبية مبسطة)**, issue date, due date, currency, total before VAT, discount value, VAT value, VAT rate, total after VAT, payment method, notes.
- **FR-102** Extract **line items (بنود)**: item name, quantity, unit, unit price, line value, line VAT.
- **FR-103** Per-field confidence and processing status on every extraction.
- **FR-104** Auto-create a **draft** purchase record with all data + attached original file + saved extracted text + per-field confidence + status = pending approval. No final record without user approval.
- **FR-105** Validate: tax-number format, no negative values, line-items total reconciles with invoice total, correct VAT computation, compare supplier against the suppliers master, suggest the correct supplier on name mismatch.
- **FR-106** Duplicate detection across invoice number, tax number, supplier name, invoice date, amount, and file fingerprint (hash). On high similarity: block, show original, explain suspicion, allow confirmed override with recorded reason.
- **FR-107** Review/approval screen (per 001 FR-005) with invoice image, extracted data, per-field confidence, unconfirmed fields, edit, re-extract, approve, reject, save-draft.
- **FR-108** Error screen (per 001 FR-005) for unreadable invoices.
- **FR-109** Reports dashboard: daily/monthly invoice counts, total purchases, total VAT, duplicates, rejected, needs-review, average processing time, AI success rate, top suppliers, top items, interactive charts; PDF/XLSX export.

## Key entities
- Staging (invoices DB): extend `invoices` (+invoice_type, currency, discount_total, commercial_registration, payment_method, due_date, field_confidence, file_hash, version); new `invoice_items`.
- Business (main DB): new `suppliers`, `purchase_items`, `purchase_attach`; extend `purchase` (+vat_amount, amount_before_vat, discount_total, currency, invoice_type, payment_method, commercial_registration, supplier_id, due_date).

## Reuse map
`InvoiceExtractionService`, `InvoicePipeline`, `InvoicePurchaseMapper` (extend), `resources/prompts/invoice-extraction.md`, existing validation + dedup helpers, `PurchaseController`.
