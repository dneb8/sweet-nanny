<?php

namespace App\Http\Controllers;

use App\Models\Nanny;
use App\Http\Requests\Nanny\{CreateNannyRequest, UpdateNannyRequest};
use App\Services\NannyService;
use Inertia\{Inertia, Response};


class NannyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NannyService $nannyService): Response
    {
        // Gate::authorize('viewAny', User::class);

        $sortables = ['email'];
        $searchables = ['name', 'surnames'];
        $nannies = $nannyService->indexFetch();

        return Inertia::render('Nanny/Index', [
            'nannies' => $nannies,
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
    public function store(CreateNannyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Nanny $nanny)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nanny $nanny)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNannyRequest $request, Nanny $nanny)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nanny $nanny)
    {
        //
    }
}
