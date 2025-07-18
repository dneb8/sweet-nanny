<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Career;
use App\Models\Nanny;

class CareerNannySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nannies = Nanny::all();
        $careers = Career::all();

        // Asegúrate de que haya datos previos
        if ($nannies->isEmpty() || $careers->isEmpty()) {
            $this->command->warn('No hay nannies o careers en la BD. Seedéalos primero.');
            return;
        }

        foreach ($nannies as $nanny) {
            // Asignar entre 1 y 3 carreras aleatorias a cada niñera
            $selectedCareers = $careers->random(rand(1, 3));

            foreach ($selectedCareers as $career) {
                $nanny->careers()->attach($career->id, [
                    'degree' => fake()->randomElement(['Bachelor', 'Master', 'PhD']),
                    'status' => fake()->randomElement(['En curso, Egreso','Titulacion']),
                    'institution' => fake()->company(),
                ]);
            }
        }
    }
}
