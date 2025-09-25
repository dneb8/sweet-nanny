<?php

namespace App\Http\Controllers;

use App\Models\Nanny;
use App\Http\Requests\Nanny\{CreateNannyRequest, UpdateNannyRequest};
use App\Services\NannyService;
use Inertia\{Inertia, Response};
use App\Http\Requests\Nanny\AsyncQualitiesRequest;
use Illuminate\Http\JsonResponse;


class NannyController extends Controller
{
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
        return Inertia::render('Nanny/Show', [
            'nanny' => $nanny->load([
                'user',
                'address',
                'courses',
                'careers',         
                'qualities',
                'reviews',
                'bookingServices.booking', 
            ]),
        ]);
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

public function asyncQualities(AsyncQualitiesRequest $request, Nanny $nanny): JsonResponse
{
    $validated = $request->validated();

    // Sync asegura que se agregan nuevas y se eliminan las que no están en el array
    $nanny->qualities()->sync($validated['qualities']);

    // Devolver las cualidades actualizadas
    return response()->json([
        'message' => 'Cualidades actualizadas correctamente',
        'qualities' => $nanny->qualities()->pluck('name'), // solo los nombres
    ]);
}

}
