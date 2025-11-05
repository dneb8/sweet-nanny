<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewService
{
    /**
     * Obtener todos los reviews en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {
        $reviews = Review::query()
            ->with(['reviewable.user.roles'])
            ->orderBy('created_at', 'desc');

        $sortables = ['created_at', 'rating', 'approved'];
        $searchables = ['comments','reviewable.user.name', 'reviewable.user.email'];

        return Fetcher::for($reviews)
            ->allowFilters([
                'approved' => [
                    'using' => fn (Filter $filter) => $filter->usingScope('filtrarPorApproved'),
                ],
                'role' => [
                    'as' => 'reviewable.user.roles.name',
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(12);
    }

    /**
     * Toggle approved status
     */
    public function toggleApproved(Review $review): Review
    {
        $review->update([
            'approved' => !$review->approved,
        ]);

        return $review->fresh();
    }
}
