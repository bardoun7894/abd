<?php

use App\Models\AiSubscription;
use App\Services\AiSubscriptionGate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Spec 007 — AI subscription enforcement: blocks extraction when expired or
// quota-exhausted, allows when active/within-date/under-quota, tracks usage,
// and supports renewal. Boots the app (facades/config) but NOT
// RefreshDatabase — this app's legacy tables aren't migration-managed — and
// creates just the ai_subscriptions table in beforeEach, mirroring
// tests/Unit/SettingsServiceTest.php.
uses(Tests\TestCase::class);

beforeEach(function () {
    if (! Schema::hasTable('ai_subscriptions')) {
        Schema::create('ai_subscriptions', function ($t) {
            $t->id();
            $t->boolean('active')->default(true);
            $t->date('starts_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->unsignedInteger('quota_pages')->nullable();
            $t->unsignedInteger('used_pages')->default(0);
            $t->dateTime('renewed_at')->nullable();
            $t->timestamps();
        });
    }
    DB::table('ai_subscriptions')->truncate();
});

function makeAiSubscription(array $attrs = []): AiSubscription
{
    return AiSubscription::create(array_merge([
        'active' => true,
        'expires_at' => now()->addDays(30)->toDateString(),
        'quota_pages' => 100,
        'used_pages' => 0,
    ], $attrs));
}

it('blocks when the subscription is expired', function () {
    $sub = makeAiSubscription(['expires_at' => now()->subDay()->toDateString()]);

    expect($sub->isExpired())->toBeTrue();
    expect($sub->isBlocked())->toBeTrue();
});

it('blocks when used_pages has reached or exceeded quota_pages', function () {
    $sub = makeAiSubscription(['quota_pages' => 10, 'used_pages' => 10]);

    expect($sub->quotaExhausted())->toBeTrue();
    expect($sub->isBlocked())->toBeTrue();
});

it('allows when active, within date, and under quota', function () {
    $sub = makeAiSubscription(['quota_pages' => 10, 'used_pages' => 5]);

    expect($sub->isBlocked())->toBeFalse();
});

it('blocks when inactive even if within date and under quota', function () {
    $sub = makeAiSubscription(['active' => false]);

    expect($sub->isBlocked())->toBeTrue();
});

it('unlimited quota (null quota_pages) is never exhausted', function () {
    $sub = makeAiSubscription(['quota_pages' => null, 'used_pages' => 999999]);

    expect($sub->quotaExhausted())->toBeFalse();
    expect($sub->isBlocked())->toBeFalse();
});

it('increments used_pages via recordUsage', function () {
    $sub = makeAiSubscription(['used_pages' => 3]);

    $sub->recordUsage(2);

    expect($sub->fresh()->used_pages)->toBe(5);
});

it('assertAllowed throws an Arabic message when blocked', function () {
    makeAiSubscription(['active' => false]);

    expect(fn () => (new AiSubscriptionGate())->assertAllowed())
        ->toThrow(RuntimeException::class, 'انتهى اشتراك الذكاء الاصطناعي أو نفدت الحصة — يرجى تجديد الاشتراك');
});

it('assertAllowed does not throw when allowed', function () {
    makeAiSubscription();

    (new AiSubscriptionGate())->assertAllowed();
})->throwsNoExceptions();

it('recordPages increments usage on the current subscription', function () {
    makeAiSubscription(['used_pages' => 0]);

    (new AiSubscriptionGate())->recordPages(3);

    expect(AiSubscription::current()->used_pages)->toBe(3);
});

it('renew resets used_pages and moves expires_at, stamping renewed_at', function () {
    $sub = makeAiSubscription(['used_pages' => 90, 'quota_pages' => 100, 'expires_at' => now()->subDay()->toDateString()]);

    $newExpiry = now()->addYear()->toDateString();
    $sub->active = true;
    $sub->expires_at = $newExpiry;
    $sub->used_pages = 0;
    $sub->renewed_at = now();
    $sub->save();

    $fresh = $sub->fresh();
    expect($fresh->used_pages)->toBe(0)
        ->and($fresh->expires_at->toDateString())->toBe($newExpiry)
        ->and($fresh->renewed_at)->not->toBeNull()
        ->and($fresh->isBlocked())->toBeFalse();
});

it('remainingPages returns null for unlimited quota', function () {
    $sub = makeAiSubscription(['quota_pages' => null]);

    expect($sub->remainingPages())->toBeNull();
});

it('remainingPages returns the difference for a limited quota', function () {
    $sub = makeAiSubscription(['quota_pages' => 10, 'used_pages' => 3]);

    expect($sub->remainingPages())->toBe(7);
});

it('remainingDays returns null when the subscription never expires', function () {
    $sub = makeAiSubscription(['expires_at' => null]);

    expect($sub->remainingDays())->toBeNull();
});

it('current() creates a sensible unblocked default row when none exists', function () {
    $sub = AiSubscription::current();

    expect($sub->isBlocked())->toBeFalse()
        ->and($sub->active)->toBeTrue();
});

it('current() is fail-safe (unblocked) when the table does not exist', function () {
    Schema::dropIfExists('ai_subscriptions');

    $sub = AiSubscription::current();

    expect($sub->isBlocked())->toBeFalse();

    // restore for any later test in this run
    Schema::create('ai_subscriptions', function ($t) {
        $t->id();
        $t->boolean('active')->default(true);
        $t->date('starts_at')->nullable();
        $t->date('expires_at')->nullable();
        $t->unsignedInteger('quota_pages')->nullable();
        $t->unsignedInteger('used_pages')->default(0);
        $t->dateTime('renewed_at')->nullable();
        $t->timestamps();
    });
});
