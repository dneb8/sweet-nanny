<?php

namespace App\Http\Controllers;

use App\Http\Requests\Career\{CreateCareerRequest, UpdateCareerRequest};
use App\Models\Career;
use App\Services\CareerService;
use Illuminate\Http\RedirectResponse;

class CareerController extends Controller
{
    /**
     * Crea una nueva carrera
     */
    public function store(CareerService $careerService, CreateCareerRequest $request): RedirectResponse
    {
        // Gate::authorize('create', Career::class);

        $careerService->createCareer($request);

        return redirect()->back()->with('message', [
            'title' => 'Carrera creada',
            'description' => 'La carrera ha sido creada correctamente.',
        ]);
    }

    /**
     * Actualiza una carrera existente
     */
    public function update(CareerService $careerService, UpdateCareerRequest $request, Career $career): RedirectResponse
    {
        // Gate::authorize('edit', Career::class);

        $careerService->updateCareer($career, $request);

        return redirect()->back()->with('message', [
            'title' => 'Carrera actualizada',
            'description' => 'La carrera ha sido actualizada correctamente.',
        ]);
    }

    /**
     * Elimina una carrera
     */
    public function destroy(Career $career): RedirectResponse
    {
        // Gate::authorize('delete', Career::class);

        $career->delete();

        return redirect()->back()->with('message', [
            'title' => 'Carrera eliminada',
            'description' => 'La carrera ha sido eliminada correctamente.',
        ]);
    }
}
