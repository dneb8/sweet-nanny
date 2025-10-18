<?php

use App\Enums\User\RoleEnum;
use App\Models\Address;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::create(['name' => RoleEnum::ADMIN->value]);
    Role::create(['name' => RoleEnum::TUTOR->value]);
    Role::create(['name' => RoleEnum::NANNY->value]);

    // Create an admin user to act as
    $this->admin = User::factory()->create([
        'email' => 'admin@test.com',
        'email_verified_at' => now(),
    ]);
    $this->admin->assignRole(RoleEnum::ADMIN->value);
});

test('tutor show page can be accessed by authenticated user', function () {
    // Create a user with tutor role
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole(RoleEnum::TUTOR->value);

    // Create tutor
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
        'emergency_contact' => 'Jane Doe',
        'emergency_number' => '+1234567890',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', $tutor->ulid));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Tutor/Show'));
});

test('tutor show returns 404 for non-existent tutor', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', 'non-existent-ulid'));

    $response->assertStatus(404);
});

test('tutor show includes user information', function () {
    // Create a user with tutor role
    $user = User::factory()->create([
        'name' => 'John',
        'surnames' => 'Doe',
        'email' => 'john.doe@example.com',
        'email_verified_at' => now(),
    ]);
    $user->assignRole(RoleEnum::TUTOR->value);

    // Create tutor
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', $tutor->ulid));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('tutor.user')
            ->where('tutor.user.name', 'John')
            ->where('tutor.user.surnames', 'Doe')
            ->where('tutor.user.email', 'john.doe@example.com')
    );
});

test('tutor show includes address information when available', function () {
    // Create address
    $address = Address::factory()->create([
        'street' => '123 Main St',
        'city' => 'Test City',
        'state' => 'Test State',
        'postal_code' => '12345',
    ]);

    // Create a user with address
    $user = User::factory()->create([
        'address_id' => $address->id,
        'email_verified_at' => now(),
    ]);
    $user->assignRole(RoleEnum::TUTOR->value);

    // Create tutor
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', $tutor->ulid));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->has('tutor.user.address')
            ->where('tutor.user.address.street', '123 Main St')
            ->where('tutor.user.address.city', 'Test City')
    );
});

test('tutor show includes emergency contact information', function () {
    // Create a user with tutor role
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole(RoleEnum::TUTOR->value);

    // Create tutor with emergency contact
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
        'emergency_contact' => 'Emergency Contact Name',
        'emergency_number' => '+1987654321',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', $tutor->ulid));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => 
        $page->where('tutor.emergency_contact', 'Emergency Contact Name')
            ->where('tutor.emergency_number', '+1987654321')
    );
});

test('tutor show avoids N+1 queries', function () {
    // Create a user with tutor role and address
    $address = Address::factory()->create();
    $user = User::factory()->create([
        'address_id' => $address->id,
        'email_verified_at' => now(),
    ]);
    $user->assignRole(RoleEnum::TUTOR->value);

    // Create tutor
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
    ]);

    // Enable query log
    \Illuminate\Support\Facades\DB::enableQueryLog();

    $response = $this->actingAs($this->admin)
        ->get(route('tutors.show', $tutor->ulid));

    $queries = \Illuminate\Support\Facades\DB::getQueryLog();
    
    // Should have minimal queries (tutor with eager loaded relations + auth queries)
    // Typically: 1 for tutor with relations, 1-2 for auth, 1-2 for permissions
    expect(count($queries))->toBeLessThan(10);

    $response->assertStatus(200);
});

test('unauthenticated users cannot access tutor show', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::TUTOR->value);
    
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->get(route('tutors.show', $tutor->ulid));

    $response->assertRedirect(route('login'));
});

test('unverified users cannot access tutor show', function () {
    // Create unverified user
    $unverifiedUser = User::factory()->create([
        'email_verified_at' => null,
    ]);
    $unverifiedUser->assignRole(RoleEnum::TUTOR->value);

    $user = User::factory()->create();
    $user->assignRole(RoleEnum::TUTOR->value);
    
    $tutor = Tutor::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($unverifiedUser)
        ->get(route('tutors.show', $tutor->ulid));

    $response->assertRedirect(route('verification.notice'));
});
