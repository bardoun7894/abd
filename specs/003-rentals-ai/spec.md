# Feature Spec 003 — Rentals AI (وحدة الإيجارات)

**Status:** Draft · **Depends on:** 001 (shared platform) · **Engine:** Google Gemini

## Overview
A new module that OCR-reads lease contracts, auto-creates a contract (pending approval), generates a payment schedule, sends smart due/expiry alerts (in-app + email + SMS), provides analytics, and an unprocessed-contracts screen. Reuses the shared extraction platform (001).

## User scenarios
- Upload a lease PDF/image → system extracts contract fields with per-field confidence → draft contract created → user reviews/approves → payment schedule auto-generated.
- The system sends alerts before/at/after each payment due date and before/at contract expiry and renewal, through the app bell, email, and SMS.
- A manager views analytics: collection rate, revenue, active/expired/renewable/troubled contracts, top/late tenants, revenue forecast.

## Functional requirements
- **FR-201** Extract from lease (PDF/images): contract number, tenant name, tenant ID/CR number, landlord data, property number, rented unit, property type, address, start date, end date, duration, rent value, number of payments, payment value, due dates, deposit, payment method, renewal terms, cancellation terms, rent-increase terms, extra financial clauses.
- **FR-202** Per-field confidence and status; auto-create a **draft** contract with attached original + saved extracted text; no final record without user approval.
- **FR-203** Auto-generate the full **payment schedule** linked to the contract: payment number, due date, amount, status, paid date, remaining, penalties.
- **FR-204** Smart alerts: payment reminders at 10 days / 5 days / due day / after delay; contract alerts at 30 days / 15 days before end, at end, at renewal, and on overdue payments. Delivered in-app + email + SMS.
- **FR-205** Analytics dashboard: collection rate, monthly/annual revenue, ended/active/renewable/troubled contracts, most-committed tenants, most-late tenants, average payment period, revenue forecast, AI collection-trend analysis.
- **FR-206** Unprocessed-contracts screen: original contract, read %, failure reasons, unread fields, edit, re-run AI, approve after edit.

## Key entities
- Staging (invoices DB): `lease_batches`, `lease_extractions` (+field_confidence, raw_json, status, version).
- Business (main DB): `lease_contracts`, `lease_payments`.
- Command: `leases:scan-alerts` (daily, registered in `app/Console/Kernel.php`).

## Reuse map
Shared platform (001): `AbstractDocumentExtractor`, review/error framework, `AlertDispatcher`. Existing seed `shop_rent` / `shop_rentpay` (optional bridge); expiry-bucket SQL idiom (`Shop.php:186`); bell notifications.

## Out of scope
- Migrating existing `shop_rent` data (optional later bridge).
