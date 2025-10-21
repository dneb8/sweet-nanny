<?php

namespace Database\Factories;

use App\Enums\Address\TypeEnum;
use App\Models\Address;
use App\Models\Tutor;
use App\Models\Nanny;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /** @var class-string<\App\Models\Address> */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * Nota: NO seteamos addressable_* por defecto; se deben crear
     * SIEMPRE a través de la relación polimórfica o usando los helpers forX().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Selecciona un tipo válido del enum (string backeado)
        $type = $this->faker->randomElement(TypeEnum::values());

        return [
            'postal_code'     => $this->faker->postcode(),
            'street'          => $this->faker->streetName(),
            'neighborhood'    => $this->faker->citySuffix(),
            'type'            => $type = $this->faker->randomElement(\App\Enums\Address\TypeEnum::values()),
            'other_type'      => $type === 'other' ? $this->faker->word() : null,
            'internal_number' => $this->faker->optional()->buildingNumber(),
        ];
    }

    /**
     * Helper: asociar a un Tutor via relación polimórfica.
     */
    public function forTutor(Tutor $tutor): static
    {
        return $this->for($tutor, 'addressable');
    }

    /**
     * Helper: asociar a una Nanny via relación polimórfica.
     */
    public function forNanny(Nanny $nanny): static
    {
        return $this->for($nanny, 'addressable');
    }

    /**
     * Helper: asociar a una Booking (morphOne).
     */
    public function forBooking(Booking $booking): static
    {
        return $this->for($booking, 'addressable');
    }
}
