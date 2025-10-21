<?php

namespace App\Services;

use App\Http\Requests\Career\CreateCareerRequest;
use App\Http\Requests\Career\UpdateCareerRequest;
use App\Models\Career;

class CareerService
{
    /**
     * Crea una carrera
     */
    public function createCareer(CreateCareerRequest $request): Career
    {
        $validated = $request->safe();

        // Crear la carrera básica
        $career = Career::create([
            'name' => $validated->name,
            'area' => $validated->area,
        ]);

        // Si se envía información de la relación Nanny-Career
        if (isset($validated->nanny_id)) {
            $career->nannies()->attach($validated->nanny_id, [
                'degree' => $validated->degree ?? null,
                'status' => $validated->status ?? null,
                'institution' => $validated->institution ?? null,
            ]);
        }

        return $career;
    }

    /**
     * Actualiza una carrera existente
     */
    public function updateCareer(Career $career, UpdateCareerRequest $request): void
    {
        $validated = $request->safe();

        // Actualizar datos de la carrera
        $career->update([
            'name' => $validated->name,
            'area' => $validated->area,
        ]);

        // Actualizar relación con Nanny si se envía
        if (isset($validated->nanny_id)) {

            $nanny = \App\Models\Nanny::findOrFail($validated->nanny_id);
            $nanny->careers()->updateExistingPivot(
                $career->id, 
                [
                    'degree' => $validated->degree ?? null,
                    'status' => $validated->status ?? null,
                    'institution' => $validated->institution ?? null,
                ]
            );
        }
    }

    /**
     * Elimina una carrera
     */
    public function deleteCareer(Career $career): void
    {
        // Eliminar relaciones con Nanny primero (pivot)
        $career->nannies()->detach();

        // Eliminar la carrera
        $career->delete();
    }
}
