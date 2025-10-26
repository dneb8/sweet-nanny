<?php

namespace Database\Factories;

use App\Models\{Course, Nanny};
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Course\NameEnum;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    // Lista de cursos reales y organizaciones mexicanas
    protected $courses = [
        ['organization' => 'Cruz Roja Mexicana'],
        ['organization' => 'Universidad Pedagógica Nacional'],
        ['organization' => 'Instituto de Psicología Infantil'],
        ['organization' => 'Universidad Autónoma de México'],
        ['organization' => 'Protección Civil'],
        ['organization' => 'Centro de Estudios Infantiles'],
        ['organization' => 'Universidad Nacional Autónoma de México'],
        ['organization' => 'Centro de Educación Infantil'],
        ['organization' => 'Escuela de Pedagogía'],
        ['organization' => 'Cruz Roja Mexicana'],
        ['organization' => 'CECATI 190'],
        ['organization' => 'CECATI 16'],
        ['organization' => 'CECATI 15'],
        ['organization' => 'SEP'],
        ['organization' => 'CONALEP'],
        ['organization' => 'Tecnológico de Monterrey'],
        ['organization' => 'Casa de Cultura'],
    ];

    public function definition(): array
    {
        // Elegir un curso al azar de la lista
        $course = $this->faker->randomElement($this->courses);

        return [
            'name' => $this->faker->randomElement(NameEnum::values()),
            'organization' => $course['organization'],
            'date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
        ];
    }
}

