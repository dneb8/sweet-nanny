<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles if they don't exist
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'nanny']);
    Role::firstOrCreate(['name' => 'tutor']);
});

test('admin can view any users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue();
});

test('nanny cannot view any users', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    expect(Gate::forUser($nanny)->allows('viewAny', User::class))->toBeFalse();
});

test('tutor cannot view any users', function () {
    $tutor = User::factory()->create();
    $tutor->assignRole('tutor');

    expect(Gate::forUser($tutor)->allows('viewAny', User::class))->toBeFalse();
});

test('admin can create users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('create', User::class))->toBeTrue();
});

test('nanny cannot create users', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    expect(Gate::forUser($nanny)->allows('create', User::class))->toBeFalse();
});

test('tutor cannot create users', function () {
    $tutor = User::factory()->create();
    $tutor->assignRole('tutor');

    expect(Gate::forUser($tutor)->allows('create', User::class))->toBeFalse();
});

test('admin can update users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($admin)->allows('update', $targetUser))->toBeTrue();
});

test('nanny cannot update users', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($nanny)->allows('update', $targetUser))->toBeFalse();
});

test('tutor cannot update users', function () {
    $tutor = User::factory()->create();
    $tutor->assignRole('tutor');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($tutor)->allows('update', $targetUser))->toBeFalse();
});

test('admin can delete users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($admin)->allows('delete', $targetUser))->toBeTrue();
});

test('nanny cannot delete users', function () {
    $nanny = User::factory()->create();
    $nanny->assignRole('nanny');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($nanny)->allows('delete', $targetUser))->toBeFalse();
});

test('tutor cannot delete users', function () {
    $tutor = User::factory()->create();
    $tutor->assignRole('tutor');

    $targetUser = User::factory()->create();

    expect(Gate::forUser($tutor)->allows('delete', $targetUser))->toBeFalse();
});
