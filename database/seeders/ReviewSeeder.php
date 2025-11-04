<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Tutor;
use App\Models\Nanny;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Reseñas predefinidas para niñeras y tutores
        $nannyReviews = [
            ['type' => Nanny::class, 'rating' => 4, 'comment' => 'Muy buena, aunque puede mejorar algunos detalles.'],
            ['type' => Nanny::class, 'rating' => 4, 'comment' => 'Responsable y cariñosa, pero llegó unos minutos tarde.'],
            ['type' => Nanny::class, 'rating' => 5, 'comment' => 'Excelente niñera, siempre atenta y puntual.'],
            ['type' => Nanny::class, 'rating' => 5, 'comment' => 'Mis hijos la adoran, servicio impecable.'],
            ['type' => Nanny::class, 'rating' => 5, 'comment' => 'No podríamos estar más contentos, ¡recomendada!'],
        ];

        $tutorReviews = [
            ['type' => Tutor::class, 'rating' => 1, 'comment' => 'Situación complicada, no recomendaría trabajar con esta familia.'],
            ['type' => Tutor::class, 'rating' => 2, 'comment' => 'Poco claros con las instrucciones y horarios.'],
            ['type' => Tutor::class, 'rating' => 3, 'comment' => 'Experiencia neutral, nada fuera de lo común.'],
            ['type' => Tutor::class, 'rating' => 4, 'comment' => 'Familia amable y organizada, fue un gusto trabajar con ellos.'],
            ['type' => Tutor::class, 'rating' => 5, 'comment' => 'Todo excelente, comunicación y trato impecables.'],
        ];

        // Combinar y mezclar aleatoriamente las reseñas
        $reviews = array_merge($nannyReviews, $tutorReviews);
        shuffle($reviews);

        foreach ($reviews as $data) {
            // Obtener o crear un ID válido para el tipo de modelo
            if ($data['type'] === Nanny::class) {
                $reviewableId = Nanny::inRandomOrder()->first()?->id
                    ?? Nanny::factory()->create()->id;
            } else {
                $reviewableId = Tutor::inRandomOrder()->first()?->id
                    ?? Tutor::factory()->create()->id;
            }

            Review::create([
                'reviewable_type' => $data['type'],
                'reviewable_id'   => $reviewableId,
                'rating'          => $data['rating'],
                'comments'        => $data['comment'],
            ]);
        }
    }
}
