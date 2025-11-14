<?php

namespace Database\Seeders;

use App\Enums\Children\KinkshipEnum;
use App\Models\Child;
use App\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ChildSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'Mateo','Sofía','Valentina','Emiliano','Isabella','Diego','Camila','Sebastián',
            'Lucía','Alejandro','Mariana','Daniel','Victoria','Julián','Fernanda','Gabriel',
            'Renata','Santiago','Carla','Bruno',
        ];

        $kinkships = KinkshipEnum::values();

        Tutor::with('user')->chunk(100, function ($tutors) use ($firstNames, $kinkships) {
            foreach ($tutors as $tutor) {
                // 1 a 5 niños por tutor
                $childrenCount = rand(1, 5);

                for ($i = 0; $i < $childrenCount; $i++) {
                    $childName = Arr::random($firstNames);

                    Child::create([
                        'tutor_id' => $tutor->id,
                        'name' => trim($childName),
                        'birthdate' => now()->subYears(rand(1, 12))->format('Y-m-d'),
                        'kinkship' => $kinkships[array_rand($kinkships)],
                    ]);
                }
            }
        });
    }
}
