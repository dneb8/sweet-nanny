<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
});

test('user can upload avatar', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(1024); // 1MB

    $response = $this
        ->actingAs($user)
        ->post('/settings/profile/avatar', [
            'avatar' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('info', 'Tu imagen se subió. Te notificaremos cuando esté validada.')
        ->assertRedirect('/settings/profile');

    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();
});

test('avatar must be an image', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 1024);

    $response = $this
        ->actingAs($user)
        ->post('/settings/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertSessionHasErrors('avatar');
});

test('avatar must not exceed 4MB', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg')->size(5120); // 5MB

    $response = $this
        ->actingAs($user)
        ->post('/settings/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertSessionHasErrors('avatar');
});

test('avatar must be jpeg, png, jpg or webp', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.gif');

    $response = $this
        ->actingAs($user)
        ->post('/settings/profile/avatar', [
            'avatar' => $file,
        ]);

    $response->assertSessionHasErrors('avatar');
});

test('avatar upload replaces existing avatar', function () {
    $user = User::factory()->create();

    // Upload first avatar
    $firstFile = UploadedFile::fake()->image('avatar1.jpg');
    $this->actingAs($user)->post('/settings/profile/avatar', ['avatar' => $firstFile]);

    $firstMediaId = $user->fresh()->getFirstMedia('images')->id;

    // Upload second avatar
    $secondFile = UploadedFile::fake()->image('avatar2.jpg');
    $this->actingAs($user)->post('/settings/profile/avatar', ['avatar' => $secondFile]);

    $user->refresh();

    // Should only have one image in the collection
    expect($user->getMedia('images'))->toHaveCount(1);

    // And it should be a different image
    expect($user->getFirstMedia('images')->id)->not->toBe($firstMediaId);
});

test('user can delete avatar', function () {
    $user = User::factory()->create();

    // Upload an avatar first
    $file = UploadedFile::fake()->image('avatar.jpg');
    $this->actingAs($user)->post('/settings/profile/avatar', ['avatar' => $file]);

    expect($user->fresh()->getFirstMedia('images'))->not->toBeNull();

    // Delete the avatar
    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile/avatar');

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Foto de perfil eliminada correctamente.')
        ->assertRedirect('/settings/profile');

    expect($user->fresh()->getFirstMedia('images'))->toBeNull();
});

test('avatar url is returned when user has avatar', function () {
    Storage::fake('s3');
    $user = User::factory()->create();

    // Upload an avatar
    $file = UploadedFile::fake()->image('avatar.jpg');
    $user->addMedia($file)->toMediaCollection('images', 's3');

    $avatarUrl = $user->getFirstMediaUrl('images');

    expect($avatarUrl)->not->toBeEmpty();
});

test('avatar url is empty when no avatar is uploaded', function () {
    Storage::fake('s3');
    $user = User::factory()->create();

    $avatarUrl = $user->getFirstMediaUrl('images');

    expect($avatarUrl)->toBeEmpty();
});
