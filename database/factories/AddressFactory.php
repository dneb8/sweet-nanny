<?php

namespace Database\Factories;

use App\Enums\Address\TypeEnum;
use App\Enums\Address\ZoneEnum;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Nanny;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /** @var class-string<\App\Models\Address> */
    protected $model = Address::class;

    public function definition(): array
    {
        // Rangos de códigos postales por zona (usar string como clave)
        $zones = [
            ZoneEnum::GUADALAJARA->value => [44000, 44999],
            ZoneEnum::ZAPOPAN->value => [45000, 45246],
            ZoneEnum::TONALA->value => [45400, 45430],
            ZoneEnum::TLAQUEPAQUE->value => [45500, 45639],
            ZoneEnum::TLAJOMULCO->value => [45640, 45680],
        ];

        // Usar el postal_code si viene por state(), si no, generar aleatorio
        $postalCode = $this->attributes['postal_code'] ?? $this->faker->numberBetween(44100, 45899);

        // Determinar la zona según el CP
        $zone = null;
        foreach ($zones as $zoneValue => [$min, $max]) {
            if ($postalCode >= $min && $postalCode <= $max) {
                $zone = $zoneValue; // ahora es string ('guadalajara', 'zapopan', etc)
                break;
            }
        }

        // Si el CP no entra en ningún rango, asignar Guadalajara por default
        if (! $zone) {
            $zone = ZoneEnum::GUADALAJARA->value;
        }

        $type = $this->faker->randomElement(TypeEnum::values());

        $street = $this->faker->streetName();
        $externalNumber = $this->faker->buildingNumber();
        $neighborhood = $this->faker->citySuffix();

        return [
            'postal_code' => (string) $postalCode,
            'street' => $street,
            'name' => $street.' #'.$externalNumber.' — '.$neighborhood,
            'neighborhood' => $neighborhood,
            'zone' => $zone,
            'type' => $type,
            'internal_number' => $this->faker->optional()->buildingNumber(),
            'external_number' => $externalNumber,
        ];
    }

    public function forTutor(Tutor $tutor): static
    {
        return $this->for($tutor, 'addressable');
    }

    public function forNanny(Nanny $nanny): static
    {
        return $this->for($nanny, 'addressable');
    }

    public function forBooking(Booking $booking): static
    {
        return $this->for($booking, 'addressable');
    }
}
