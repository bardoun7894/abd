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
        'retries' => env('GEMINI_RETRIES', 4),
        // Pricing for the cost estimate shown in the UI (USD per 1M tokens).
        'price_in_per_m' => env('GEMINI_PRICE_IN', 1.50),
        'price_out_per_m' => env('GEMINI_PRICE_OUT', 9.00),
        'usd_to_sar' => env('USD_TO_SAR', 3.75),
    ],

];
