<?php

/*
|--------------------------------------------------------------------------
| AI document storage (Spec 005 T-C1)
|--------------------------------------------------------------------------
|
| Additive, opt-in configuration for the DocumentStorage service. Defaults
| to OFF (plaintext, legacy behaviour unchanged) so existing AI upload
| controllers (Expense/Shop/Vehicle/Workers/Moraslat/Purchase) are not
| affected until a controller is explicitly migrated to use
| App\Services\DocumentStorage::store().
|
| This does NOT touch the invoice/lease pipeline (InvoiceExtractionService,
| InvoicePipeline, LeasePipeline) — those keep writing to
| public_path('uploads/invoices|leases/**') exactly as before.
|
*/

return [

    // Master switch. When false, DocumentStorage::store() still writes
    // outside the public web root (storage/app/private/uploads/**) but
    // does NOT encrypt — lets a controller adopt the private path first,
    // then flip encryption on separately once verified.
    'encrypt_at_rest' => (bool) env('DOCUMENTS_ENCRYPT_AT_REST', false),

    // New, non-web-reachable root for documents stored via DocumentStorage.
    'private_root' => storage_path('app/private/uploads'),

    // Legacy web-reachable root kept for backward-compat reads of files
    // written by the existing controllers before/without this service.
    'legacy_public_root' => public_path('uploads'),

    // Whitelist of module keys allowed through documents.serve — prevents
    // path traversal / arbitrary directory reads via the module segment.
    'allowed_modules' => [
        'expense',
        'shop',
        'vehicles',
        'workers',
        'moraslat',
        'purchase',
    ],
];
