<?php

use App\Enums\User\RoleEnum;
use App\Models\Nanny;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
});

test('user can upload their own avatar via users route', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(1024); // 1MB

    $response = $this
        ->actingAs($user)
        ->post(route('users.avatar.update', $user), [
            'avatar' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('info', 'Tu imagen se subió. Te notificaremos cuando esté validada.');

    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();
});

test('admin can upload avatar for another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetUser = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(1024);

    $response = $this
        ->actingAs($admin)
        ->post(route('users.avatar.update', $targetUser), [
            'avatar' => $file,
        ]);

    $response->assertSessionHasNoErrors();
    expect($targetUser->fresh()->getFirstMedia('images'))->not->toBeNull();
});

test('user cannot upload avatar for another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this
        ->actingAs($user1)
        ->post(route('users.avatar.update', $user2), [
            'avatar' => $file,
        ]);

    $response->assertForbidden();
});

test('nanny can upload their avatar', function () {
    $nanny = Nanny::factory()->create();
    $user = $nanny->user;
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(1024);

    $response = $this
        ->actingAs($user)
        ->post(route('users.avatar.update', $user), [
            'avatar' => $file,
        ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();
});

test('tutor can upload their avatar', function () {
    $tutor = Tutor::factory()->create();
    $user = $tutor->user;
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(1024);

    $response = $this
        ->actingAs($user)
        ->post(route('users.avatar.update', $user), [
            'avatar' => $file,
        ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();
});

test('user can delete their own avatar via users route', function () {
    $user = User::factory()->create();

    // Upload an avatar first
    $file = UploadedFile::fake()->image('avatar.jpg');
    $this->actingAs($user)->post(route('users.avatar.update', $user), ['avatar' => $file]);

    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();

    // Delete the avatar
    $response = $this
        ->actingAs($user)
        ->delete(route('users.avatar.delete', $user));

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Foto de perfil eliminada correctamente.');

    expect($user->fresh()->getFirstMedia('images'))->toBeNull();
});

test('admin can delete avatar for another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetUser = User::factory()->create();

    // Upload an avatar for target user
    $file = UploadedFile::fake()->image('avatar.jpg');
    $this->actingAs($admin)->post(route('users.avatar.update', $targetUser), ['avatar' => $file]);

    expect($targetUser->fresh()->getFirstMedia('images'))->not->toBeNull();

    // Admin deletes the avatar
    $response = $this
        ->actingAs($admin)
        ->delete(route('users.avatar.delete', $targetUser));

    $response->assertSessionHasNoErrors();
    expect($targetUser->fresh()->getFirstMedia('images'))->toBeNull();
});

test('user cannot delete avatar for another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Upload an avatar for user2
    $file = UploadedFile::fake()->image('avatar.jpg');
    $this->actingAs($user2)->post(route('users.avatar.update', $user2), ['avatar' => $file]);

    // user1 tries to delete user2's avatar
    $response = $this
        ->actingAs($user1)
        ->delete(route('users.avatar.delete', $user2));

    $response->assertForbidden();
});

test('avatar validation rules apply to user avatar upload', function () {
    $user = User::factory()->create();

    // Test non-image file
    $file = UploadedFile::fake()->create('document.pdf', 1024);
    $response = $this
        ->actingAs($user)
        ->post(route('users.avatar.update', $user), ['avatar' => $file]);
    $response->assertSessionHasErrors('avatar');

    // Test file too large (>4MB)
    $file = UploadedFile::fake()->image('avatar.jpg')->size(5120);
    $response = $this
        ->actingAs($user)
        ->post(route('users.avatar.update', $user), ['avatar' => $file]);
    $response->assertSessionHasErrors('avatar');
});
