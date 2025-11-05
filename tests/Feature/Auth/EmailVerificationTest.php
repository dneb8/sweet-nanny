<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification notification can be sent', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post('/email/verification-notification');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verification notification respects throttling', function () {
    $user = User::factory()->unverified()->create();

    // First request should succeed
    $response = $this->actingAs($user)->post('/email/verification-notification');
    $response->assertSessionHas('status', __('verification-link-sent'));

    // Subsequent requests within throttle period should still succeed but be rate limited
    // The throttle is set to 6 requests per minute in routes/auth.php
    for ($i = 0; $i < 6; $i++) {
        $response = $this->actingAs($user)->post('/email/verification-notification');
    }

    // The 7th request should be throttled
    $response = $this->actingAs($user)->post('/email/verification-notification');
    $response->assertStatus(429); // Too Many Requests
});
