<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tutor\CreateTutorRequest;
use App\Http\Requests\Tutor\UpdateTutorRequest;
use App\Http\Traits\HandlesAvatarValidation;
use App\Models\Tutor;
use App\Services\TutorService;
use Inertia\Inertia;
use Inertia\Response;

class TutorController extends Controller
{
    use HandlesAvatarValidation;
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

        $tutor = $tutorService->getShowData($tutor->ulid);

        $tutor->load([
            'user.roles',
            'user.media' => fn ($q) => $q->where('collection_name', 'images'),
            'addresses',
            'reviews',
            'bookings.bookingAppointments',
        ]);

        // Trigger validation if needed for pending avatars
        $this->kickoffAvatarValidationIfNeeded($tutor->user);

        return Inertia::render('Tutor/Show', [
            'tutor' => $tutor,
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
