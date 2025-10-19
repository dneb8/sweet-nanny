<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ChildService;

class ChildController extends Controller
{
    /**
     * Crea un niño
     */
    public function store(ChildService $childService, Request $request): JsonResponse
    {
        $child = $childService->createChild($request);

        return response()->json($child, 201);
    }

    /**
     * Actualiza un niño
     */
    public function update(Child $child, ChildService $childService, Request $request): JsonResponse
    {
        $child = $childService->updateChild($child, $request);
        
        return response()->json($child);
    }

    /**
     * Elimina un niño
     */
    public function destroy(Child $child, ChildService $childService): JsonResponse
    {
        $childService->deleteChild($child);

        return response()->json(['deleted' => true]);
    }
}
