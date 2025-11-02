<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

class ReviewController extends Controller
{
    public function index(ReviewService $reviewService): Response
    {
        $reviews = $reviewService->indexFetch();

        return Inertia::render('Review/Index', ['reviews' => $reviews]);
    }

    public function toggleApproved(Review $review, ReviewService $reviewService)
    {
        $reviewService->toggleApproved($review);

        return back()->with('success', 'Estado de aprobación actualizado correctamente.');
    }
}

