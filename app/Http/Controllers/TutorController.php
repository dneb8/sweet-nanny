<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tutor\{CreateTutorRequest, UpdateTutorRequest};
use App\Models\Tutor;
use App\Services\TutorService;
use Inertia\{Inertia, Response};


class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TutorService $tutorService): Response
    {
        // Gate::authorize('viewAny', User::class);

        $sortables = ['email'];
        $searchables = ['name', 'surnames'];
        $tutors = $tutorService->indexFetch();

        return Inertia::render('Tutor/Index', [
            'tutors' => $tutors,
            'sortables' => $sortables,
            'searchables' => $searchables,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTutorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tutor $tutor, TutorService $tutorService): Response
    {
        // $this->authorize('view', $tutor);

        $tutorData = $tutorService->getShowData($tutor->ulid);

        return Inertia::render('Tutor/Show', [
            'tutor' => $tutorData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tutor $tutor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTutorRequest $request, Tutor $tutor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tutor $tutor)
    {
        //
    }
}
