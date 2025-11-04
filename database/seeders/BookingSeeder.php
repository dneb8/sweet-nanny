<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\BookingAppointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

// Enums
use App\Enums\Nanny\QualityEnum;
use App\Enums\Career\NameCareerEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            'Se cuidará a un niño pequeño que debe dormir a las 8pm y además es necesario que no coma dulces',
            'Se recomienda el baile para ella y supervisión en sus actividades artísticas',
            'Necesita ayuda con tareas de la escuela y cuidado mientras se realizan actividades físicas',
            'Se cuidará a dos niños y se fomentará la lectura antes de dormir',
            'Atención especial para niño con alergia a frutos secos, se fomentará la creatividad',
            'Se requiere supervisión mientras juega y se enseñan hábitos de higiene',
            'Niño necesita apoyo en sus deberes y tiempo de juego tranquilo',
            'Se cuidará mientras practica música y pintura, fomentando su desarrollo artístico',
            'Atención a rutinas de alimentación saludable y ejercicio diario',
            'Se sugiere lectura y juegos educativos, sin uso de pantallas',
            'Niño pequeño que debe dormir temprano y seguir su rutina de alimentación',
            'Se fomentará la creatividad con manualidades y juegos didácticos',
            'Supervisión durante actividades deportivas y cuidado especial en meriendas',
            'Niño con intereses en ciencias y naturaleza, se dará tiempo para experimentos seguros',
            'Se cuidará mientras se realizan actividades musicales y manualidades',
            'Rutina de sueño estricta y alimentación supervisada, con juego tranquilo',
            'Se recomienda baile y juegos de coordinación motriz',
            'Apoyo en tareas escolares y actividades de concentración',
            'Niña necesita supervisión en actividades artísticas y lectura',
            'Cuidado general con énfasis en hábitos saludables y juego creativo',
        ];

        $qualitiesValues = array_map(fn($c) => $c->value, QualityEnum::cases());
        $careersValues   = array_map(fn($c) => $c->value, NameCareerEnum::cases());
        $coursesValues   = array_map(fn($c) => $c->value, CourseNameEnum::cases());

        foreach ($descriptions as $desc) {
            // Tutor con al menos una dirección
            $tutor = Tutor::with(['addresses', 'children'])->inRandomOrder()->first();
            if (! $tutor || $tutor->addresses->isEmpty()) {
                continue;
            }

            /** @var \App\Models\Booking $booking */
            $booking = Booking::factory()->create([
                'tutor_id'    => $tutor->id,
                'description' => $desc,
                'qualities'   => Arr::random($qualitiesValues, rand(1, min(8, count($qualitiesValues)))),
                'careers'     => Arr::random($careersValues,   rand(1, min(2, count($careersValues)))),
                'courses'     => Arr::random($coursesValues,   rand(1, min(2, count($coursesValues)))),
            ]);

            // 1–5 citas por booking
            $appointmentsCount = rand(1, 5);

            for ($k = 0; $k < $appointmentsCount; $k++) {
                /** @var \App\Models\BookingAppointment $appointment */
                $appointment = BookingAppointment::factory()->create([
                    'booking_id' => $booking->id,
                ]);

                // Address: una del tutor
                $address = $tutor->addresses()->inRandomOrder()->first();
                if ($address) {
                    $appointment->addresses()->syncWithoutDetaching([$address->id]);
                }

                // Children: 1–4 que pertenezcan al tutor
                if ($tutor->children && $tutor->children->isNotEmpty()) {
                    $childrenCount = rand(1, min(4, $tutor->children->count()));
                    $childIds = $tutor->children->random($childrenCount)->pluck('id')->all();
                    $appointment->children()->syncWithoutDetaching($childIds);
                }
            }

            $booking->recurrent = $appointmentsCount >= 2;
            $booking->save();
        }
    }
}
