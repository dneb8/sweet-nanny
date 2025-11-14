<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NannySearchService;

class NannySearchController extends Controller
{
    protected $nannySearchService;

    public function __construct(NannySearchService $nannySearchService)
    {
        $this->nannySearchService = $nannySearchService;
    }

    /**
     * Endpoint para entrenar el modelo en Flask con los datos de nannies
     * POST /api/nannies/train
     */
    public function trainModel()
    {
        $response = $this->nannySearchService->sendAllNanniesToFlask();

        return response()->json($response);
    }

    /**
     * Endpoint para buscar niñeras compatibles con filtros
     * POST /api/nannies/search
     */

    public function search(Request $request)
    {
        $request->validate([
            'qualities'     => 'nullable|array',
            'courses'       => 'nullable|array',
            'career'        => 'nullable|array',
            'zone'          => 'nullable|string',
            'availability'  => 'nullable|boolean',
        ]);

        // Enviar filtros a Flask
        $response = $this->nannySearchService->sendFiltersToFlask($request->all());

        // Obtener los IDs retornados por Flask
        $nannyIds = $response['nanny_ids'] ?? [];

        // Buscar las niñeras en la base de datos
        $top3Nannies = \App\Models\Nanny::whereIn('id', $nannyIds)->get();

        // Retornar datos completos al frontend
        return response()->json([
            'filters_applied' => $response['filters_applied'] ?? [],
            'count' => count($nannyIds),
            'top3Nannies' => $top3Nannies->values()->toArray(),
        ]);
    }


    
}
