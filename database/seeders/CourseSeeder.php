<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Nanny;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Borramos cualquier curso previo
        Course::truncate();

        // Obtener todas las niñeras
        $nannies = Nanny::all();

        foreach ($nannies as $nanny) {
            // Cada niñera tiene entre 1 y 4 cursos
            $numCourses = rand(1, 4);

            Course::factory()
                ->count($numCourses)
                ->create([
                    'nanny_id' => $nanny->id,
                ]);
        }
    }
}
