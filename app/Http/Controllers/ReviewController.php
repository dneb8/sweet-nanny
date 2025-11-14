<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\{Inertia, Response};

class ReviewController extends Controller
{
    public function index(ReviewService $reviewService): Response
    {
        $reviews = $reviewService->indexFetch();

        return Inertia::render('Review/Index', ['reviews' => $reviews]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reviewable_type' => ['required', 'string', 'in:App\\Models\\Tutor,App\\Models\\Nanny'],
            'reviewable_id' => ['required', 'integer', 'exists:' . ($request->reviewable_type === 'App\\Models\\Tutor' ? 'tutors' : 'nannies') . ',id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        // Always set approved to false for user submissions
        $validated['approved'] = false;

        $review = Review::create($validated);

        return back()
            ->with('success', 'Reseña enviada correctamente. Será revisada por un administrador.')
            ->with('recent', ['review' => $review]);
    }

    public function toggleApproved(Review $review, ReviewService $reviewService)
    {
        $reviewService->toggleApproved($review);

        return back()->with('success', 'Estado de aprobación actualizado correctamente.');
    }
}

