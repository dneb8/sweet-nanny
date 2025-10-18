<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tutor;
use App\Models\Child;
use App\Models\Booking;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the tutor role
        Role::create(['name' => 'tutor', 'guard_name' => 'web']);
    }

    protected function createTutorWithChild(): array
    {
        $user = User::factory()->create();
        $tutor = Tutor::factory()->create(['user_id' => $user->id]);
        $child = Child::factory()->create(['tutor_id' => $tutor->id]);

        return ['user' => $user, 'tutor' => $tutor, 'child' => $child];
    }

    public function test_booking_can_be_created_with_polymorphic_address(): void
    {
        $data = $this->createTutorWithChild();
        
        $startDate = now()->addDay();
        $endDate = $startDate->copy()->addHours(2);
        
        $response = $this->actingAs($data['user'])->post(route('bookings.store'), [
            'booking' => [
                'tutor_id' => $data['tutor']->id,
                'description' => 'Test booking with polymorphic address',
                'recurrent' => false,
                'children' => [(string) $data['child']->id],
                'qualities' => ['Responsable', 'Paciente'],
                'degree' => 'Licenciatura',
                'courses' => ['Primeros Auxilios', 'RCP Infantil'],
            ],
            'appointments' => [
                [
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date' => $endDate->toDateTimeString(),
                    'duration' => 2,
                ],
            ],
            'address' => [
                'postal_code' => '44100',
                'street' => 'Calle Test 123',
                'neighborhood' => 'Colonia Test',
                'type' => 'casa',
            ],
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('bookings', [
            'tutor_id' => $data['tutor']->id,
            'description' => 'Test booking with polymorphic address',
        ]);

        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals(['Responsable', 'Paciente'], $booking->qualities);
        $this->assertEquals('Licenciatura', $booking->degree);
        $this->assertEquals(['Primeros Auxilios', 'RCP Infantil'], $booking->courses);

        // Check polymorphic address was created
        $this->assertNotNull($booking->addressPolymorphic);
        $this->assertEquals('44100', $booking->addressPolymorphic->postal_code);
    }

    public function test_booking_can_be_updated(): void
    {
        $data = $this->createTutorWithChild();
        
        $booking = Booking::factory()->create([
            'tutor_id' => $data['tutor']->id,
            'description' => 'Original description',
        ]);

        $booking->children()->attach($data['child']->id);

        $startDate = now()->addDay();
        $endDate = $startDate->copy()->addHours(3);

        $response = $this->actingAs($data['user'])->put(route('bookings.update', $booking->id), [
            'booking' => [
                'tutor_id' => $data['tutor']->id,
                'description' => 'Updated description',
                'recurrent' => false,
                'child_ids' => [(string) $data['child']->id],
                'qualities' => ['Creativa'],
                'degree' => 'Maestría',
                'courses' => ['Desarrollo Infantil'],
            ],
            'appointments' => [
                [
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date' => $endDate->toDateTimeString(),
                    'duration' => 3,
                ],
            ],
            'address' => [
                'postal_code' => '44200',
                'street' => 'Updated Street 456',
                'neighborhood' => 'Updated Neighborhood',
                'type' => 'departamento',
            ],
        ]);

        $response->assertRedirect();
        
        $booking->refresh();
        $this->assertEquals('Updated description', $booking->description);
        $this->assertEquals(['Creativa'], $booking->qualities);
        $this->assertEquals('Maestría', $booking->degree);
        $this->assertEquals(['Desarrollo Infantil'], $booking->courses);
    }

    public function test_booking_can_be_deleted(): void
    {
        $data = $this->createTutorWithChild();
        
        $booking = Booking::factory()->create([
            'tutor_id' => $data['tutor']->id,
        ]);

        $response = $this->actingAs($data['user'])->delete(route('bookings.destroy', $booking->id));

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_tutor_can_have_multiple_polymorphic_addresses(): void
    {
        $data = $this->createTutorWithChild();
        
        // Create multiple addresses for tutor
        $address1 = Address::factory()->create([
            'addressable_type' => Tutor::class,
            'addressable_id' => $data['tutor']->id,
            'postal_code' => '44100',
        ]);

        $address2 = Address::factory()->create([
            'addressable_type' => Tutor::class,
            'addressable_id' => $data['tutor']->id,
            'postal_code' => '44200',
        ]);

        $addresses = $data['tutor']->addresses;
        
        $this->assertCount(2, $addresses);
        $this->assertTrue($addresses->contains('id', $address1->id));
        $this->assertTrue($addresses->contains('id', $address2->id));
    }
}
