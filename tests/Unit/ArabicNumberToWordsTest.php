<?php

use App\Support\ArabicNumberToWords as W;

uses(Tests\TestCase::class);

/**
 * تفقيط is printed on a financial document as the guard against a tampered
 * figure, so a wrong word is worse than no words at all. These cases pin the
 * Arabic grammar rules a generic library gets wrong: 1 and 2 expressed by the
 * noun itself, 3-10 taking the plural, 11+ reverting to the singular, and the
 * unit spoken before the ten.
 */
it('handles the units and teens', function () {
    expect(W::integer(0))->toBe('صفر');
    expect(W::integer(1))->toBe('واحد');
    expect(W::integer(3))->toBe('ثلاثة');
    expect(W::integer(11))->toBe('أحد عشر');
    expect(W::integer(19))->toBe('تسعة عشر');
});

it('says the unit before the ten', function () {
    expect(W::integer(20))->toBe('عشرون');
    expect(W::integer(25))->toBe('خمسة وعشرون');
    expect(W::integer(99))->toBe('تسعة وتسعون');
});

it('handles hundreds, including the irregular مئتان', function () {
    expect(W::integer(100))->toBe('مئة');
    expect(W::integer(200))->toBe('مئتان');
    expect(W::integer(300))->toBe('ثلاثمئة');
    expect(W::integer(905))->toBe('تسعمئة وخمسة');
});

it('applies the thousand grammar: ألف / ألفان / آلاف / ألفاً', function () {
    expect(W::integer(1000))->toBe('ألف');            // never "واحد ألف"
    expect(W::integer(2000))->toBe('ألفان');          // dual, not "اثنان ألف"
    expect(W::integer(3000))->toBe('ثلاثة آلاف');      // 3-10 -> plural
    expect(W::integer(11000))->toBe('أحد عشر ألف');   // 11+ -> bare singular (construct, reads with the currency)
    expect(W::integer(20000))->toBe('عشرون ألف');
});

it('applies the same grammar to millions', function () {
    expect(W::integer(1000000))->toBe('مليون');
    expect(W::integer(2000000))->toBe('مليونان');
    expect(W::integer(5000000))->toBe('خمسة ملايين');
});

it('joins descending scales with و', function () {
    expect(W::integer(1905))->toBe('ألف وتسعمئة وخمسة');
    expect(W::integer(20000))->toBe('عشرون ألف');
    expect(W::integer(1234))->toBe('ألف ومئتان وأربعة وثلاثون');
});

it('builds the full voucher phrase', function () {
    expect(W::amount(20000))->toBe('فقط عشرون ألف ريال لا غير');
    expect(W::amount(0))->toBe('فقط صفر ريال لا غير');
});

it('adds the هللة part only when there are cents', function () {
    expect(W::amount(100.00))->toBe('فقط مئة ريال لا غير');
    expect(W::amount(100.50))->toBe('فقط مئة ريال وخمسون هللة لا غير');
    // Guards the classic float drift: 20.10 must not become "تسع هللات".
    expect(W::amount(20.10))->toBe('فقط عشرون ريال وعشرة هللة لا غير');
});

it('returns null rather than a wrong figure for unusable input', function () {
    expect(W::amount(null))->toBeNull();
    expect(W::amount('abc'))->toBeNull();
    expect(W::amount(-5))->toBeNull();
    expect(W::amount(1_000_000_000))->toBeNull();   // beyond the supported range
});
