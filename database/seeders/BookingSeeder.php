<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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

        $qualitiesList = [
            'empatica','creativa','paciente','carinosa','observadora',
            'asertiva','proactiva','flexible','ludica','bilingue'
        ];

        $careersList = [
            'pedagogia','psicologia','enfermeria','docencia','nutricion',
            'trabajo_social','psicopedagogia','terapia_psicomotriz','pediatria','artes_escenicas_danza'
        ];

        $coursesList = [
            'primeros_auxilios','cuidado_infantil','desarrollo_infantil',
            'nutricion_y_alimentacion','educacion_y_aprendizaje','psicologia_infantil',
            'disciplina_y_comportamiento','lactancia_y_cuidado_bebes','inclusion_y_diversidad','comunicacion_y_lenguaje'
        ];

        for ($i = 0; $i < 20; $i++) {
            $tutor = Tutor::with('addresses')->inRandomOrder()->first();

            if ($tutor && $tutor->addresses->isNotEmpty()) {
                $address = $tutor->addresses()->inRandomOrder()->first();

                Booking::factory()->create([
                    'tutor_id' => $tutor->id,
                    'address_id' => $address->id,
                    'description' => $descriptions[$i],
                    'recurrent' => $i % 2 === 0,
                    'qualities' => Arr::random($qualitiesList, rand(1, 8)),
                    'careers' => Arr::random($careersList, rand(1, 2)),
                    'courses' => Arr::random($coursesList, rand(1, 2)),
                ]);
            }
        }
    }
}
