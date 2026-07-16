<?php

// ZATCA (Saudi tax authority) Phase-1 simplified e-invoice QR settings.
// Seller name + VAT registration number MUST come from here (env), never
// hardcoded in code — see App\Services\ZatcaQrGenerator.
return [
    'seller_name' => env('ZATCA_SELLER_NAME', ''),
    'vat_number' => env('ZATCA_VAT_NUMBER', ''),
];
