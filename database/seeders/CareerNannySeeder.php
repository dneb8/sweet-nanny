<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Career;
use App\Models\Nanny;
use App\Enums\Career\StatusEnum;
use App\Enums\Career\DegreeEnum;

class CareerNannySeeder extends Seeder
{
    public function run(): void
    {
        $nannies = Nanny::all();
        $careers = Career::all();

        if ($nannies->isEmpty() || $careers->isEmpty()) {
            $this->command->warn('No hay nannies o careers en la BD. Seedéalos primero.');
            return;
        }

        // Lista de instituciones reales de Guadalajara
        $institutionsGDL = [
            'Universidad de Guadalajara (UDG)',
            'ITESO',
            'Tecnológico de Monterrey, Campus Guadalajara',
            'Universidad del Valle de Atemajac (UNIVA)',
            'CUCEA - UDG',
            'Universidad Panamericana, Campus Guadalajara',
            'Universidad Autónoma de Guadalajara (UAG)',
        ];

        foreach ($nannies as $nanny) {
            // Elegimos entre 1 y 3 carreras al azar, sin repetir
            $selectedCareers = $careers->shuffle()->take(rand(1, 3));

            foreach ($selectedCareers as $career) {
                // Evitar duplicados
                if (!$nanny->careers()->where('career_id', $career->id)->exists()) {
                    $nanny->careers()->attach($career->id, [
                        'status' => StatusEnum::cases()[array_rand(StatusEnum::cases())]->value,
                        'degree' => DegreeEnum::cases()[array_rand(DegreeEnum::cases())]->value,
                        'institution' => $institutionsGDL[array_rand($institutionsGDL)],
                    ]);
                }
            }
        }
    }
}
