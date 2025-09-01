<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nanny;
use App\Models\Quality;
use App\Enums\Nanny\QualityEnum;

class NannyQualitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1️⃣ Crear las qualities en la BD según el enum
        foreach (QualityEnum::cases() as $case) {
            Quality::firstOrCreate([
                'name' => $case->value
            ]);
        }

        // 2️⃣ Obtener todos los IDs de las qualities ya creadas
        $qualityIds = Quality::pluck('id')->toArray();

        // 3️⃣ Asignar aleatoriamente 1-3 qualities a cada nanny
        Nanny::all()->each(function (Nanny $nanny) use ($qualityIds) {
            $randomIds = collect($qualityIds)
                ->random(rand(1, 7)) // 1 a 3 qualities aleatorias
                ->toArray();

            $nanny->qualities()->attach($randomIds);
        });
    }
}
