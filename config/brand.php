<?php

/*
 * Company identity (per-instance branding).
 *
 * The app is deployed for more than one company off the same codebase, so the
 * company name MUST NOT be hardcoded in views/controllers — it is read from
 * here, and App\Services\Settings::applyToConfig() overrides these values at
 * boot with whatever the admin set in Settings → هوية الشركة (DB app_settings,
 * keys company_name_ar / company_name_en).
 *
 * Resolution order, lowest to highest: this file's default → .env → app_settings.
 * The defaults below keep the original instance (شركة صباح النور) rendering
 * exactly as before when nothing is configured.
 *
 * The logo is deliberately NOT listed here: it is a per-instance FILE swap under
 * public/assets/media/logos/ (gitignored), plus resources/images/logo-voucher.png
 * for the سند PDF masthead. Deploying must never overwrite a company's own logo.
 */
return [
    'name_ar' => env('BRAND_NAME_AR', 'شركة صباح النور'),
    'name_en' => env('BRAND_NAME_EN', 'Sabah Alnoor CO.'),

    /*
     * Visual theme. public/css/app-ui.css ships the نور الصباح blue identity as
     * its baseline; setting this to 'sabah-emerald' additionally loads
     * public/css/brand/sabah-emerald.css AFTER it, restoring the original
     * صباح النور emerald + warm-paper look. One codebase, two identities.
     *
     * 'noor-blue' (default) → blue/navy, no override file
     * 'sabah-emerald'       → emerald/warm, loads the override
     */
    'theme' => env('BRAND_THEME', 'noor-blue'),

    /*
     * The company's real logo file, used for the auth-page brand mark. Lives
     * under public/assets/media/logos/ which is gitignored — each instance keeps
     * its OWN logo on its own server, and a deploy must never overwrite it.
     */
    'logo' => env('BRAND_LOGO', 'assets/media/logos/logo.jpg'),

    /*
     * Server-rendered documents (TCPDF vouchers, legacy report PDFs, Excel via
     * ExcelReportStyler) cannot read CSS variables, so they take their colours
     * from here instead. Keep these in step with the active theme's tokens.
     */
    'pdf' => [
        'primary' => env('BRAND_PDF_PRIMARY', '#1477AE'),
        'deep' => env('BRAND_PDF_DEEP', '#25435D'),
        'tint' => env('BRAND_PDF_TINT', '#E8F1F7'),
        'line' => env('BRAND_PDF_LINE', '#C6D4DF'),

        /*
         * Text and accent tokens. Only the four above are exposed in Settings →
         * هوية الشركة (App\Services\Settings::applyToConfig) because they are the
         * ones that visibly carry the brand; these are env-only so an instance
         * CAN retune them without adding four more admin fields nobody uses.
         */
        'ink' => env('BRAND_PDF_INK', '#1B2C3A'),
        'muted' => env('BRAND_PDF_MUTED', '#5A6B7A'),
        'accent' => env('BRAND_PDF_ACCENT', '#F78F13'),
        'accent_alt' => env('BRAND_PDF_ACCENT_ALT', '#60BA49'),
    ],
];
