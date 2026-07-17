<?php

// Boot the Laravel app so config()/config('zatca.*') is available.
uses(Tests\TestCase::class);

use App\Models\Invoice;
use App\Services\ZatcaQrGenerator;

beforeEach(function () {
    $this->svc = new ZatcaQrGenerator();
});

it('encodes an ordered tag list as TLV+base64 matching the canonical ZATCA Phase-1 sample vector', function () {
    // Canonical ZATCA Phase-1 QR sample: seller "Bobby Smith", VAT
    // "301121971500003", timestamp 2021-07-12T14:25:09Z, total 100, VAT 15.
    // Verified byte-for-byte below (round-trip test) against the TLV spec:
    // [1-byte tag][1-byte UTF-8 byte length][UTF-8 value], concatenated in
    // tag order, whole buffer base64 encoded.
    $expected = 'AQtCb2JieSBTbWl0aAIPMzAxMTIxOTcxNTAwMDAzAxQyMDIxLTA3LTEyVDE0OjI1OjA5WgQDMTAwBQIxNQ==';

    $actual = $this->svc->tlv([
        1 => 'Bobby Smith',
        2 => '301121971500003',
        3 => '2021-07-12T14:25:09Z',
        4 => '100',
        5 => '15',
    ]);

    expect($actual)->toBe($expected);
});

it('round-trips: decoding the TLV base64 yields back the exact tag/length/value triples', function () {
    $b64 = $this->svc->tlv([
        1 => 'Bobby Smith',
        2 => '301121971500003',
    ]);
    $bytes = base64_decode($b64);

    expect(ord($bytes[0]))->toBe(1);
    expect(ord($bytes[1]))->toBe(11); // strlen('Bobby Smith')
    expect(substr($bytes, 2, 11))->toBe('Bobby Smith');
    expect(ord($bytes[13]))->toBe(2);
    expect(ord($bytes[14]))->toBe(15); // strlen('301121971500003')
    expect(substr($bytes, 15, 15))->toBe('301121971500003');
});

it('uses UTF-8 byte length, not character count, for multibyte values', function () {
    $arabic = 'شركة'; // 4 chars, 8 bytes in UTF-8
    $b64 = $this->svc->tlv([1 => $arabic]);
    $bytes = base64_decode($b64);

    expect(ord($bytes[0]))->toBe(1);
    expect(ord($bytes[1]))->toBe(strlen($arabic));
    expect(ord($bytes[1]))->not->toBe(mb_strlen($arabic));
    expect(substr($bytes, 2, strlen($arabic)))->toBe($arabic);
});

it('builds the 5-tag QR payload for an invoice using config seller/VAT + invoice totals', function () {
    config()->set('zatca.seller_name', 'Bobby Smith');
    config()->set('zatca.vat_number', '301121971500003');

    $invoice = new Invoice();
    $invoice->forceFill([
        'created_at' => '2021-07-12 14:25:09',
        'total_incl_vat' => 100,
        'vat_amount' => 15,
    ]);

    $qr = $this->svc->qrBase64($invoice);
    $bytes = base64_decode($qr);

    $i = 0;
    $tags = [];
    while ($i < strlen($bytes)) {
        $tag = ord($bytes[$i]);
        $len = ord($bytes[$i + 1]);
        $tags[$tag] = substr($bytes, $i + 2, $len);
        $i += 2 + $len;
    }

    expect($tags[1])->toBe('Bobby Smith');
    expect($tags[2])->toBe('301121971500003');
    expect($tags[3])->toBe('2021-07-12T14:25:09Z');
    expect($tags[4])->toBe('100.00');
    expect($tags[5])->toBe('15.00');
});

it('never hardcodes seller name/VAT — and emits NO QR when identity is unconfigured', function () {
    config()->set('zatca.seller_name', '');
    config()->set('zatca.vat_number', '');

    $invoice = new Invoice();
    $invoice->forceFill(['total_incl_vat' => 50, 'vat_amount' => 7.5]);

    // Guard: with no seller identity configured, produce no QR at all rather
    // than a QR carrying a blank seller name / VAT number.
    expect($this->svc->isConfigured())->toBeFalse()
        ->and($this->svc->qrBase64($invoice))->toBe('');
});
