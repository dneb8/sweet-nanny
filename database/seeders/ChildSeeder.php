<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class ChildSeeder extends Seeder
{
    public function run(): void
    {
        // Lista de nombres de pila reales (puedes agregar más)
        $firstNames = [
            'Mateo', 'Sofía', 'Valentina', 'Emiliano', 'Isabella', 'Diego', 'Camila', 'Sebastián',
            'Lucía', 'Alejandro', 'Mariana', 'Daniel', 'Victoria', 'Julián', 'Fernanda', 'Gabriel',
            'Renata', 'Santiago', 'Carla', 'Bruno'
        ];

        // Creamos 20 niños
        for ($i = 0; $i < 20; $i++) {
            $tutor = Tutor::inRandomOrder()->first();

            // Si no hay tutor, saltamos esta iteración
            if (!$tutor) {
                continue;
            }
            // Generamos un nombre completo usando los apellidos del tutor
            $childName = $firstNames[array_rand($firstNames)] . ' ' . $tutor->user->surnames;

            \App\Models\Child::create([
                'tutor_id' => $tutor->id,
                'name' => $childName,
                'birthdate' => now()->subYears(rand(1, 12))->format('Y-m-d'), // Edad entre 1 y 12 años
                'kinkship' => \App\Enums\Children\KinkshipEnum::values()[array_rand(\App\Enums\Children\KinkshipEnum::values())],
            ]);
        }
    }
}
