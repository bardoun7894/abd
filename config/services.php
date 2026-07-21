<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Google Gemini — used by the AI invoice-extraction feature.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Default to the stable, generally-available model. gemini-3.5-flash is
        // real but returns 429/quota on limited-tier keys — using it as the code
        // default made every AI path fail closed when app_settings was empty.
        'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-flash-lite-latest'),
        'thinking_level' => env('GEMINI_THINKING_LEVEL', 'minimal'), // minimal|low|medium|high (Gemini 3.x) — clear scans
        // Adaptive thinking: re-read a BAD scan with deeper thinking. Clear scans stay on the
        // cheap level above; only invoices flagged for review trigger this second pass.
        'thinking_level_hard' => env('GEMINI_THINKING_LEVEL_HARD', 'low'),
        'escalate_on_review' => env('GEMINI_ESCALATE', true),
        // DPI for per-page PNG rasterization (pdftoppm). Higher = sharper but more tokens.
        'raster_dpi' => env('GEMINI_RASTER_DPI', 200),
        // Default extraction mode: 'split' (page=invoice, reliable per-invoice + own image),
        // 'grouped' (split + merge pages sharing an invoice number), or 'whole' (one AI call).
        'default_mode' => env('GEMINI_INVOICE_MODE', 'split'),
        // Stronger model used by the manual "🔍 إعادة الفحص بدقة أعلى" (smart re-scan) action —
        // a slower/pricier model the user opts into for a better read on a low-quality batch.
        'rescan_model' => env('GEMINI_RESCAN_MODEL', 'gemini-3-flash-preview'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => env('GEMINI_TIMEOUT', 120),
        // Per-page HTTP timeout and deadline buffer for AI calls inside pipelines.
        'page_timeout' => env('GEMINI_PAGE_TIMEOUT', 120),
        // Whole-batch job deadline (seconds). Feeds ProcessInvoiceBatch::$timeout and
        // the pipeline soft-deadline in tandem. Raised from the old hardcoded 1800 so
        // large (50-80 invoice) PDFs finish their tail pages instead of being force-failed.
        'batch_timeout' => env('GEMINI_BATCH_TIMEOUT', 3600),
        'retries' => env('GEMINI_RETRIES', 4),
        // Interactive (synchronous, in-request) AI extract — shop/purchase/worker/... form
        // prefill. Short fast-fail budget so a slow/overloaded model does not hold a
        // PHP-FPM worker for minutes and freeze the browser. The user can just retry.
        'interactive_timeout' => env('GEMINI_INTERACTIVE_TIMEOUT', 25),
        'interactive_retries' => env('GEMINI_INTERACTIVE_RETRIES', 2),
        // Adaptive model escalation (interactive extractors): the first pass runs on
        // the cheap default model; when the mean field_confidence is below the floor
        // (hard/unclear scan) the SAME prepared page is re-read once on a stronger
        // model with deeper thinking. Escalation never breaks extraction — on error
        // the first-pass result is kept. Thinking ladder: minimal (pass 1) → medium
        // (escalation); the manual re-scan action can go higher via rescan_model.
        'interactive_escalate' => env('GEMINI_INTERACTIVE_ESCALATE', true),
        'escalation_model' => env('GEMINI_ESCALATION_MODEL', 'gemini-3.5-flash'),
        'escalation_thinking' => env('GEMINI_ESCALATION_THINKING', 'medium'),
        'escalation_confidence_floor' => env('GEMINI_ESCALATION_CONFIDENCE', 0.5),
        // Interactive doc prep (Spec 007): only page 1 of a multi-page PDF is sent to
        // Gemini for the synchronous form-prefill path, rasterized at a lower DPI than
        // the background pipelines and images downscaled if oversized — cuts billed
        // input tokens without touching accuracy (page 1 has the data we need).
        'interactive_dpi' => env('GEMINI_INTERACTIVE_DPI', 130),
        'interactive_max_px' => env('GEMINI_INTERACTIVE_MAX_PX', 1600),
        // Leading pages sent for interactive extraction. EJAR unified leases keep
        // the financial block (annual rent, #payments, cycle) on page 3 — page-1
        // only misses the whole rent schedule. 3 pages at 130dpi ≈ ~1.7k input
        // tokens on flash-lite — still fractions of a halala per call.
        'interactive_pages' => env('GEMINI_INTERACTIVE_PAGES', 3),
        // Result cache (dedup identical calls → 0 cost on re-send) + concurrency cap
        // (a burst of AI calls can't starve PHP-FPM workers). Fail-open — any cache/DB
        // hiccup never blocks extraction.
        'cache_enabled' => env('GEMINI_CACHE_ENABLED', true),
        'cache_ttl_days' => env('GEMINI_CACHE_TTL_DAYS', 90),
        'max_concurrent' => env('GEMINI_MAX_CONCURRENT', 3),
        'slot_ttl' => env('GEMINI_SLOT_TTL', 90), // seconds; auto-releases a leaked slot
        // Pricing for the cost estimate shown in the UI (USD per 1M tokens).
        'price_in_per_m' => env('GEMINI_PRICE_IN', 1.50),
        'price_out_per_m' => env('GEMINI_PRICE_OUT', 9.00),
        // Per-model rates (July 2026) — flash-lite is 6× cheaper than 3.5-flash, so a
        // single flat rate overstates the cost of the flash-lite-first strategy.
        'model_prices' => [
            'gemini-flash-lite-latest' => [0.25, 1.50],
            'gemini-3.1-flash-lite-latest' => [0.25, 1.50],
            'gemini-3.5-flash' => [1.50, 9.00],
        ],
        'usd_to_sar' => env('USD_TO_SAR', 3.75),
    ],

];
