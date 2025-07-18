<?php

namespace Database\Seeders;

use App\Models\Nanny;
use App\Models\Quality;
use Illuminate\Database\Seeder;

class NannyQualitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $qualities = Quality::all();

        Nanny::all()->each(function ($nanny) use ($qualities) {
            $nanny->qualities()->attach(
                $qualities->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
