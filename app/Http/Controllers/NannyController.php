<?php

namespace App\Http\Controllers;

use App\Models\Nanny;
use App\Http\Requests\Nanny\{CreateNannyRequest, UpdateNannyRequest};
use App\Services\NannyService;
use App\Models\Quality; 
use Inertia\{Inertia, Response};
use App\Http\Requests\Nanny\AsyncQualitiesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


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

    // Normalizar nombres: quitar espacios extra y mayúsculas consistentes
    $qualitiesFromRequest = array_map('trim', $validated['qualities']);

    // Obtener IDs de las qualities existentes en DB que coincidan con los nombres
    $qualityIds = Quality::whereIn('name', $qualitiesFromRequest)->pluck('id')->toArray();

    // Sync con la relación belongsToMany
    $nanny->qualities()->sync($qualityIds);

    // Recargar la relación para devolver los datos actualizados
    $nanny->load('qualities');

    return response()->json([
        'message' => 'Cualidades actualizadas correctamente',
        'qualities' => $nanny->qualities->pluck('name'), // Array de strings, igual que tu frontend espera
    ]);
}


}
