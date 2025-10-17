<?php

use App\Models\User;

test('unverified users are redirected to verification notice', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/users');

    $response->assertRedirect(route('verification.notice'));
});

test('user verification status is correctly checked', function () {
    $verifiedUser = User::factory()->create();
    $unverifiedUser = User::factory()->unverified()->create();

    expect($verifiedUser->hasVerifiedEmail())->toBeTrue();
    expect($unverifiedUser->hasVerifiedEmail())->toBeFalse();
});
