<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;

/**
 * ZATCA Phase-1 (simplified tax invoice) TLV QR code generator.
 *
 * Encodes the 5 mandatory Phase-1 tags — seller name, VAT registration
 * number, invoice timestamp, invoice total (incl. VAT), VAT total — as
 * TLV ([1-byte tag][1-byte UTF-8 byte length][UTF-8 value], concatenated
 * in tag order) then base64 encodes the whole buffer, per the ZATCA
 * "Detailed E-invoicing guideline" QR code spec.
 */
class ZatcaQrGenerator
{
    /**
     * Encode an ordered [tagNumber => value] list as TLV, base64 encoded.
     * Pure/unit-testable core — no I/O, no config, no model access.
     *
     * @param  array<int, string>  $tags  Ordered map of tag number => value.
     */
    public function tlv(array $tags): string
    {
        $binary = '';
        foreach ($tags as $tagNumber => $value) {
            $value = (string) $value;
            $length = strlen($value); // UTF-8 byte length, not character count
            $binary .= chr((int) $tagNumber).chr($length).$value;
        }

        return base64_encode($binary);
    }

    /**
     * Build the ZATCA Phase-1 QR payload (base64 TLV) for one invoice.
     * Seller name + VAT registration number are OUR company's registration
     * data and always come from config('zatca.*') — never hardcoded and
     * never taken from the (supplier) invoice fields.
     */
    public function qrBase64(Invoice $invoice): string
    {
        $sellerName = (string) config('zatca.seller_name', '');
        $vatNumber = (string) config('zatca.vat_number', '');

        // Prefer the full invoice timestamp (created_at has time); the
        // `invoice_date` column is date-only (cast truncates the time).
        $moment = $invoice->created_at ?? $invoice->invoice_date ?? now();
        $timestamp = Carbon::parse($moment)->format('Y-m-d\TH:i:s\Z');

        $total = number_format((float) ($invoice->total_incl_vat ?? 0), 2, '.', '');
        $vat = number_format((float) ($invoice->vat_amount ?? 0), 2, '.', '');

        return $this->tlv([
            1 => $sellerName,
            2 => $vatNumber,
            3 => $timestamp,
            4 => $total,
            5 => $vat,
        ]);
    }
}
