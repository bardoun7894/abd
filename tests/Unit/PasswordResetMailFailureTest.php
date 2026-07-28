<?php

// Boot the Laravel app (facades + container) but DO NOT use RefreshDatabase — the
// Password broker is mocked, so this test never touches a database.
uses(Tests\TestCase::class);

use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;

afterEach(function () {
    Mockery::close();
});

// Regression (client منصور, 2026-07-28): the SMTP credentials on the noor host were
// rejected (535 Bad Credentials), sendResetLink() threw, and the exception escaped
// PasswordResetLinkController::store() as a 500 — so "نسيت كلمة المرور" showed the
// host's error page instead of telling the user anything.
test('forgot password reports a form error when the mail transport fails', function () {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new TransportException('Failed to authenticate on SMTP server'));

    $response = $this->from('/forgot-password')
                     ->post('/forgot-password', ['email' => 'aasss1449@gmail.com']);

    $response->assertRedirect('/forgot-password');
    $response->assertSessionHasErrors(['email' => __('passwords.mail_failed')]);
});

test('forgot password still reports the broker status when the mail transport works', function () {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_LINK_SENT);

    $response = $this->from('/forgot-password')
                     ->post('/forgot-password', ['email' => 'aasss1449@gmail.com']);

    $response->assertRedirect('/forgot-password');
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status', __('passwords.sent'));
});
