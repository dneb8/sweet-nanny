<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PostalCodeController extends Controller
{
    /**
     * Get postal code information including colonias (neighborhoods)
     * Data source: SEPOMEX (Mexican postal service)
     */
    public function show(string $postalCode): JsonResponse
    {
        // Validate postal code format
        if (! preg_match('/^\d{5}$/', $postalCode)) {
            return response()->json([
                'error' => 'Código postal inválido. Debe tener 5 dígitos.',
            ], 400);
        }

        // TODO: Integrate with SEPOMEX API or local database
        // For now, returning mock data structure
        // In production, this should query SEPOMEX data

        $data = $this->getMockPostalCodeData($postalCode);

        if (! $data) {
            return response()->json([
                'error' => 'Código postal no encontrado.',
            ], 404);
        }

        return response()->json($data);
    }

    /**
     * Mock data for development
     * TODO: Replace with actual SEPOMEX integration
     */
    private function getMockPostalCodeData(string $postalCode): ?array
    {
        // Sample data for common Guadalajara postal codes
        $mockData = [
            '44100' => [
                'postal_code' => '44100',
                'municipality' => 'Guadalajara',
                'state' => 'Jalisco',
                'colonias' => [
                    'Centro',
                    'Americana',
                    'Artesanos',
                    'Mexicaltzingo',
                ],
            ],
            '44160' => [
                'postal_code' => '44160',
                'municipality' => 'Guadalajara',
                'state' => 'Jalisco',
                'colonias' => [
                    'Arcos Vallarta',
                    'Jardines del Bosque',
                ],
            ],
            '45050' => [
                'postal_code' => '45050',
                'municipality' => 'Zapopan',
                'state' => 'Jalisco',
                'colonias' => [
                    'Ciudad Granja',
                    'Lomas de Tabachines',
                ],
            ],
        ];

        return $mockData[$postalCode] ?? null;
    }
}
