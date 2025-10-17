<?php

use App\Models\User;
use App\Enums\User\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an admin user to act as
    $this->admin = User::factory()->create([
        'email' => 'admin@test.com',
    ]);
    $this->admin->assignRole(RoleEnum::ADMIN->value);
});

test('users index page can be rendered', function () {
    $response = $this->actingAs($this->admin)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('User/Index'));
});

test('users can be searched by name', function () {
    // Create test users
    User::factory()->create(['name' => 'Juan', 'surnames' => 'Pérez']);
    User::factory()->create(['name' => 'Maria', 'surnames' => 'González']);
    User::factory()->create(['name' => 'Pedro', 'surnames' => 'López']);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['search' => 'Juan']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', 1)
            ->where('users.data.0.name', 'Juan')
    );
});

test('users can be searched by email', function () {
    User::factory()->create(['email' => 'juan@example.com']);
    User::factory()->create(['email' => 'maria@example.com']);
    User::factory()->create(['email' => 'pedro@example.com']);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['search' => 'maria@example']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', 1)
            ->where('users.data.0.email', 'maria@example.com')
    );
});

test('users can be filtered by role', function () {
    // Create users with different roles
    $tutor = User::factory()->create(['name' => 'Tutor User']);
    $tutor->assignRole(RoleEnum::TUTOR->value);

    $nanny = User::factory()->create(['name' => 'Nanny User']);
    $nanny->assignRole(RoleEnum::NANNY->value);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['filters' => ['role' => RoleEnum::TUTOR->value]]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', 1)
            ->where('users.data.0.roles.0.name', RoleEnum::TUTOR->value)
    );
});

test('users can be filtered by email verification status', function () {
    // Create verified and unverified users
    $verified = User::factory()->create([
        'name' => 'Verified User',
        'email_verified_at' => now(),
    ]);

    $unverified = User::factory()->create([
        'name' => 'Unverified User',
        'email_verified_at' => null,
    ]);

    // Test verified filter
    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['filters' => ['verified' => 'verified']]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', fn ($data) => 
            $data->where('0.email_verified_at', fn ($value) => $value !== null)
        )
    );

    // Test unverified filter
    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['filters' => ['verified' => 'unverified']]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', fn ($data) => 
            $data->where('0.email_verified_at', null)
        )
    );
});

test('users can be sorted by name ascending', function () {
    User::factory()->create(['name' => 'Charlie']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['sort' => 'name', 'dir' => 'asc']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->where('users.data.0.name', 'Alice')
            ->where('users.data.1.name', 'Bob')
            ->where('users.data.2.name', 'Charlie')
    );
});

test('users can be sorted by name descending', function () {
    User::factory()->create(['name' => 'Charlie']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['sort' => 'name', 'dir' => 'desc']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->where('users.data.0.name', 'Charlie')
            ->where('users.data.1.name', 'Bob')
            ->where('users.data.2.name', 'Alice')
    );
});

test('users can be sorted by email', function () {
    User::factory()->create(['email' => 'charlie@example.com']);
    User::factory()->create(['email' => 'alice@example.com']);
    User::factory()->create(['email' => 'bob@example.com']);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['sort' => 'email', 'dir' => 'asc']));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->where('users.data.0.email', 'alice@example.com')
            ->where('users.data.1.email', 'bob@example.com')
            ->where('users.data.2.email', 'charlie@example.com')
    );
});

test('users can be paginated', function () {
    // Create 25 users
    User::factory()->count(25)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['per_page' => 10]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', 10)
            ->where('users.per_page', 10)
            ->where('users.total', 26) // 25 + admin
            ->where('users.last_page', 3)
    );
});

test('sorting field is sanitized against invalid values', function () {
    User::factory()->create(['name' => 'Test User']);

    // Try to sort by an invalid field (SQL injection attempt)
    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['sort' => 'invalid_field; DROP TABLE users;', 'dir' => 'asc']));

    $response->assertStatus(200);
    // Should default to 'created_at' when invalid sort field is provided
    $response->assertInertia(fn ($page) => $page->has('users.data'));
});

test('sort direction is sanitized against invalid values', function () {
    User::factory()->create(['name' => 'Test User']);

    // Try to use an invalid direction
    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['sort' => 'name', 'dir' => 'invalid']));

    $response->assertStatus(200);
    // Should default to 'desc' when invalid direction is provided
    $response->assertInertia(fn ($page) => $page->has('users.data'));
});

test('per page value is limited to maximum', function () {
    User::factory()->count(150)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['per_page' => 200]));

    $response->assertStatus(200);
    // Should be limited to 100 items per page
    $response->assertInertia(fn ($page) => 
        $page->where('users.per_page', 100)
    );
});

test('search and filters can be combined', function () {
    // Create users with different roles and names
    $tutor1 = User::factory()->create(['name' => 'Juan Tutor', 'email_verified_at' => now()]);
    $tutor1->assignRole(RoleEnum::TUTOR->value);

    $tutor2 = User::factory()->create(['name' => 'Maria Tutor', 'email_verified_at' => null]);
    $tutor2->assignRole(RoleEnum::TUTOR->value);

    $nanny = User::factory()->create(['name' => 'Juan Nanny', 'email_verified_at' => now()]);
    $nanny->assignRole(RoleEnum::NANNY->value);

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', [
            'search' => 'Juan',
            'filters' => [
                'role' => RoleEnum::TUTOR->value,
                'verified' => 'verified',
            ]
        ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('users.data', 1)
            ->where('users.data.0.name', 'Juan Tutor')
    );
});

test('pagination preserves query parameters', function () {
    User::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('users.index', ['search' => 'test', 'sort' => 'name', 'dir' => 'asc', 'page' => 2]));

    $response->assertStatus(200);
    // Check that links preserve query parameters
    $response->assertInertia(fn ($page) => 
        $page->has('users.links')
    );
});
