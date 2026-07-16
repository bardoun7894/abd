# Spec 005 — Remaining AI Work (task tracker)

Single source of truth for what's **still missing / to add**. Everything else (Purchases AI, Rentals AI, app-wide document AI for Expense/Shop/Workers/Vehicles/Moraslat, audit, versioning, permissions, dedup, review/error/reports screens) is **built, tested (113 unit tests), and deployed** to MyContabo (http://91.230.110.187:9095).

Status key: ⬜ not started · 🟡 partial · ✅ done · 🔒 blocked on external creds

---

## A. Integrations that need YOUR credentials (code buildable now, activation blocked)

### T-A1 — SMS activation 🔒
- `SmsClient` (Taqnyat driver) + `AlertDispatcher` are **built & wired**. Lease/payment alerts already call it.
- **Blocked on:** `SMS_API_KEY` (Taqnyat bearer token) + `SMS_SENDER` (registered sender). Provide → I set them in server `.env` → send a live test SMS.
- Effort after creds: ~10 min.

### T-A2 — ZATCA Phase 1 (QR code) ⬜  *(no creds needed)*
- Generate the ZATCA Phase-1 TLV QR (seller name, VAT no., timestamp, invoice total, VAT total) on each AI-extracted / approved invoice; render on the invoice view + PDF.
- Feasible immediately from existing invoice data. Effort: ~half day.

### T-A3 — ZATCA Phase 2 (Clearance/Reporting) 🔒
- Build UBL 2.1 XML generation + cryptographic stamp + submission to the ZATCA reporting/clearance API. Wire the existing `ZatcaController` (currently a hardcoded OTP stub).
- **Blocked on:** ZATCA onboarding — your CR + OTP from the Fatoora portal → compliance CSID → production CSID + certificate. Cannot transmit without it.
- Effort: multi-day once cert is available.

## B. Missing AI modules from Spec 004 (not yet built)

### T-B1 — Violations AI ⬜
- Draft Arabic violation-notice letters + classify violation side/severity (Spec 004 C2). Follows the Expense/Moraslat pattern.

### T-B2 — Reports NL / ask-your-data ⬜
- Natural-language summaries + "اسأل بياناتك" over financial/expense/purchase aggregates (Spec 004 C3).

### T-B3 — Home AI insight card ⬜
- Monthly AI insight summary on the dashboard (Spec 004 C4).

### T-B4 — Purchase in-screen AI ⬜
- Embed the existing invoice AI **inside the real purchase (المشتريات) add form** (upload → prefill purchase form → confirm), instead of the separate module + bridge (Spec 004 A1).

### T-B5 — Shop-rent bridge ⬜
- Connect the lease AI to the **existing** `shop_rent` / `shop_rentpay` module, or migrate shop-rent onto the richer `lease_contracts`/`lease_payments` (Spec 004 A2). Design decision to confirm.

## C. Governance / security (from the original spec)

### T-C1 — File encryption at rest ⬜
- Encrypt stored documents (uploaded PDFs/images) + decrypt on serve; move uploads out of the public web root (currently `public/uploads/` is web-reachable). Touches upload + rasterize + Gemini-send + serve + reprocess paths — careful standalone task with tests.

### T-C2 — FCM push notifications ⬜
- "تطبيقات الإشعارات" push channel added to `AlertDispatcher` (in-app + email + SMS already done).

## D. Repo hygiene / pre-existing (found during deploy)

### T-D1 — Remove Debugbar from `config/app.php` ⬜
- The Debugbar provider + facade are hardcoded in `config/app.php` (dev package) → breaks prod `--no-dev` installs. Patched **only on the server**; fix in the repo.

### T-D2 — Malware-flagged dev deps ⬜
- `laravel-lang/lang` + `laravel-lang/attributes` in `composer.lock` are Packagist-flagged as malware (dev-only, excluded in prod). Review / update.

### T-D3 — Confirm Gemini model id ⬜
- `config/services.php` default is `gemini-3.5-flash`; verify it's a real current model (likely `gemini-2.0-flash`) before any live OCR run — else the API 404s.

---

## Suggested order
1. **T-D3** (confirm model id — 2 min, unblocks live testing)
2. **T-A1** (SMS — once you send creds) · **T-A2** (ZATCA QR — no creds)
3. **T-B1..B5** (finish Spec 004 modules — parallel fan-out)
4. **T-C1** (encryption) · **T-D1/T-D2** (repo hygiene)
5. **T-A3** (ZATCA Phase 2 — when cert ready) · **T-C2** (FCM)

## Blocked-on-you checklist
- [ ] Taqnyat `SMS_API_KEY` + `SMS_SENDER`  (→ T-A1)
- [ ] ZATCA certificate / CSID + OTP  (→ T-A3)
- [ ] Confirm Gemini model id to use  (→ T-D3)
