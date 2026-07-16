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
        'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-3.5-flash'),
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
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => env('GEMINI_TIMEOUT', 120),
        'retries' => env('GEMINI_RETRIES', 4),
        // Pricing for the cost estimate shown in the UI (USD per 1M tokens).
        'price_in_per_m' => env('GEMINI_PRICE_IN', 1.50),
        'price_out_per_m' => env('GEMINI_PRICE_OUT', 9.00),
        'usd_to_sar' => env('USD_TO_SAR', 3.75),
    ],

];
