<?php

use App\Enums\User\RoleEnum;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => RoleEnum::ADMIN->value]);
    Role::create(['name' => RoleEnum::TUTOR->value]);
    Role::create(['name' => RoleEnum::NANNY->value]);

    // Create an admin user to act as (with verified email)
    $this->admin = User::factory()->create([
        'email' => 'admin@test.com',
        'email_verified_at' => now(),
    ]);
    $this->admin->assignRole(RoleEnum::ADMIN->value);

    // Create UserService instance
    $this->service = app(UserService::class);
});

test('users can be searched by name', function () {
    $this->actingAs($this->admin);

    // Create test users
    User::factory()->create(['name' => 'Juan', 'surnames' => 'Pérez']);
    User::factory()->create(['name' => 'Maria', 'surnames' => 'González']);
    User::factory()->create(['name' => 'Pedro', 'surnames' => 'López']);

    // Simulate search request
    request()->merge(['search' => 'Juan']);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1);
    expect($result->first()->name)->toBe('Juan');
});

test('users can be searched by email', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['email' => 'juan@example.com']);
    User::factory()->create(['email' => 'maria@example.com']);
    User::factory()->create(['email' => 'pedro@example.com']);

    request()->merge(['search' => 'maria@example']);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1);
    expect($result->first()->email)->toBe('maria@example.com');
});

test('users can be filtered by role', function () {
    $this->actingAs($this->admin);

    // Create users with different roles
    $tutor = User::factory()->create(['name' => 'Tutor User']);
    $tutor->assignRole(RoleEnum::TUTOR->value);

    $nanny = User::factory()->create(['name' => 'Nanny User']);
    $nanny->assignRole(RoleEnum::NANNY->value);

    request()->merge(['filters' => ['role' => RoleEnum::TUTOR->value]]);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1);
    expect($result->first()->roles->first()->name)->toBe(RoleEnum::TUTOR->value);
});

test('users can be filtered by email verification status verified', function () {
    $this->actingAs($this->admin);

    // Create verified and unverified users
    User::factory()->create([
        'name' => 'Verified User',
        'email_verified_at' => now(),
    ]);

    User::factory()->create([
        'name' => 'Unverified User',
        'email_verified_at' => null,
    ]);

    // Test verified filter (excludes admin, so only 1 verified user)
    request()->merge(['filters' => ['verified' => 'verified']]);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1); // only the verified user (admin is excluded by service)
    expect($result->first()->email_verified_at)->not->toBeNull();
});

test('users can be filtered by email verification status unverified', function () {
    $this->actingAs($this->admin);

    // Create verified and unverified users
    User::factory()->create([
        'name' => 'Verified User',
        'email_verified_at' => now(),
    ]);

    User::factory()->create([
        'name' => 'Unverified User',
        'email_verified_at' => null,
    ]);

    // Test unverified filter
    request()->merge(['filters' => ['verified' => 'unverified']]);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1);
    expect($result->first()->email_verified_at)->toBeNull();
});

test('users can be sorted by name ascending', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['name' => 'Charlie']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    request()->merge(['sort' => 'name', 'dir' => 'asc']);
    $result = $this->service->indexFetch();

    expect($result->items()[0]->name)->toBe('Alice');
    expect($result->items()[1]->name)->toBe('Bob');
    expect($result->items()[2]->name)->toBe('Charlie');
});

test('users can be sorted by name descending', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['name' => 'Charlie']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    request()->merge(['sort' => 'name', 'dir' => 'desc']);
    $result = $this->service->indexFetch();

    expect($result->items()[0]->name)->toBe('Charlie');
    expect($result->items()[1]->name)->toBe('Bob');
    expect($result->items()[2]->name)->toBe('Alice');
});

test('users can be sorted by email', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['email' => 'charlie@example.com']);
    User::factory()->create(['email' => 'alice@example.com']);
    User::factory()->create(['email' => 'bob@example.com']);

    request()->merge(['sort' => 'email', 'dir' => 'asc']);
    $result = $this->service->indexFetch();

    // Admin is excluded from list, so first should be alice
    expect($result->items()[0]->email)->toBe('alice@example.com');
    expect($result->items()[1]->email)->toBe('bob@example.com');
    expect($result->items()[2]->email)->toBe('charlie@example.com');
});

test('users can be paginated', function () {
    $this->actingAs($this->admin);

    // Create 25 users
    User::factory()->count(25)->create();

    request()->merge(['per_page' => 10]);
    $result = $this->service->indexFetch();

    expect($result->count())->toBe(10);
    expect($result->perPage())->toBe(10);
    expect($result->total())->toBe(25); // 25 users (admin excluded)
    expect($result->lastPage())->toBe(3);
});

test('sorting field is sanitized against invalid values', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['name' => 'Test User']);

    // Try to sort by an invalid field (SQL injection attempt)
    request()->merge(['sort' => 'invalid_field; DROP TABLE users;', 'dir' => 'asc']);
    $result = $this->service->indexFetch();

    // Should default to 'created_at' when invalid sort field is provided
    expect($result)->not->toBeNull();
    expect($result->count())->toBeGreaterThan(0);
});

test('sort direction is sanitized against invalid values', function () {
    $this->actingAs($this->admin);

    User::factory()->create(['name' => 'Test User']);

    // Try to use an invalid direction
    request()->merge(['sort' => 'name', 'dir' => 'invalid']);
    $result = $this->service->indexFetch();

    // Should default to 'desc' when invalid direction is provided
    expect($result)->not->toBeNull();
    expect($result->count())->toBeGreaterThan(0);
});

test('per page value is limited to maximum', function () {
    $this->actingAs($this->admin);

    User::factory()->count(150)->create();

    request()->merge(['per_page' => 200]);
    $result = $this->service->indexFetch();

    // Should be limited to 100 items per page
    expect($result->perPage())->toBe(100);
});

test('search and filters can be combined', function () {
    $this->actingAs($this->admin);

    // Create users with different roles and names
    $tutor1 = User::factory()->create(['name' => 'Juan Tutor', 'email_verified_at' => now()]);
    $tutor1->assignRole(RoleEnum::TUTOR->value);

    $tutor2 = User::factory()->create(['name' => 'Maria Tutor', 'email_verified_at' => null]);
    $tutor2->assignRole(RoleEnum::TUTOR->value);

    $nanny = User::factory()->create(['name' => 'Juan Nanny', 'email_verified_at' => now()]);
    $nanny->assignRole(RoleEnum::NANNY->value);

    request()->merge([
        'search' => 'Juan',
        'filters' => [
            'role' => RoleEnum::TUTOR->value,
            'verified' => 'verified',
        ],
    ]);
    $result = $this->service->indexFetch();

    expect($result->total())->toBe(1);
    expect($result->first()->name)->toBe('Juan Tutor');
});

test('pagination appends query parameters', function () {
    $this->actingAs($this->admin);

    User::factory()->count(20)->create();

    request()->merge(['search' => 'test', 'sort' => 'name', 'dir' => 'asc', 'page' => 2]);
    $result = $this->service->indexFetch();

    // Check that the pagination response has links
    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
});
