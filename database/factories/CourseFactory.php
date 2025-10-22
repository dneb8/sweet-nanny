<?php

namespace Database\Factories;

use App\Models\{Course, Nanny};
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    // Lista de cursos reales y organizaciones mexicanas
    protected $courses = [
        ['name' => 'Primeros Auxilios', 'organization' => 'Cruz Roja Mexicana'],
        ['name' => 'Educación Infantil', 'organization' => 'Universidad Pedagógica Nacional'],
        ['name' => 'Cuidado de Niños con Necesidades Especiales', 'organization' => 'Instituto de Psicología Infantil'],
        ['name' => 'Nutrición Infantil', 'organization' => 'Universidad Autónoma de México'],
        ['name' => 'Seguridad y Prevención de Accidentes', 'organization' => 'Protección Civil'],
        ['name' => 'Desarrollo Cognitivo en Niños', 'organization' => 'Centro de Estudios Infantiles'],
        ['name' => 'Psicología Infantil Básica', 'organization' => 'Universidad Nacional Autónoma de México'],
        ['name' => 'Lenguaje y Comunicación', 'organization' => 'Centro de Educación Infantil'],
        ['name' => 'Taller de Juegos Educativos', 'organization' => 'Escuela de Pedagogía'],
        ['name' => 'Primeros Auxilios Psicológicos', 'organization' => 'Cruz Roja Mexicana'],
        ['name' => 'Danza Folclórica Mexicana', 'organization' => 'CECATI 157'],
        ['name' => 'Danza Contemporánea', 'organization' => 'Centro Cultural Ollin Yoliztli'],
        ['name' => 'Ballet Clásico', 'organization' => 'Escuela Nacional de Danza Clásica y Contemporánea'],
        ['name' => 'Música Infantil', 'organization' => 'Centro Cultural Tlalpan'],
        ['name' => 'Artes Plásticas para Niños', 'organization' => 'Museo Tamayo'],
        ['name' => 'Taller de Teatro Infantil', 'organization' => 'Instituto Nacional de Bellas Artes'],
        ['name' => 'Educación Socioemocional', 'organization' => 'SEP'],
        ['name' => 'Lengua Extranjera: Inglés Básico', 'organization' => 'CONALEP'],
        ['name' => 'Programación y Robótica Infantil', 'organization' => 'Tecnológico de Monterrey'],
        ['name' => 'Manualidades y Creatividad', 'organization' => 'Casa de Cultura'],
    ];

    public function definition(): array
    {
        // Elegir un curso al azar de la lista
        $course = $this->faker->randomElement($this->courses);

        return [
            'name' => $course['name'],
            'organization' => $course['organization'],
            'date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'nanny_id' => Nanny::inRandomOrder()->first()?->id,
        ];
    }
}

