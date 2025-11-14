<?php

namespace App\Services;

use App\Models\Nanny;
use Illuminate\Support\Facades\Http;

class NannySearchService
{
    /**
     * Devuelve todos los registros de Nanny en formato JSON
     * para depuración sin enviarlos a Flask.
     */
    public function previewAllNanniesJson()
    {
        $nannies = Nanny::with(['qualities', 'courses', 'careers', 'addresses'])->get();

        $formatted = $nannies->map(function ($nanny) {
            return [
                'id' => $nanny->id,
                'name' => $nanny->name,
                'qualities' => $nanny->qualities->pluck('name')->toArray(),
                'courses' => $nanny->courses->pluck('name')->toArray(),
                'career' => $nanny->careers->pluck('name')->toArray(),
                'zone' => optional($nanny->addresses->first())->zone ?? 'Desconocida',
                'availability' => (bool) $nanny->availability,
            ];
        })->toArray();

        $payload = ['nannies' => $formatted];


        // return si no quieres detener con dd
        return [
            'nannies' => $formatted,
            'count' => count($formatted),
        ];
    }


    /**
     * Envía todos los registros de Nanny a la API Flask
     */
    public function sendAllNanniesToFlask()
    {
         
        $nannies = Nanny::with(['qualities', 'courses', 'careers', 'addresses'])->get();

        $formatted = $nannies->map(function ($nanny) {
            return [
                'id' => $nanny->id,
                'name' => $nanny->name,
                'qualities' => $nanny->qualities->pluck('name')->toArray(),
                'courses' => $nanny->courses->pluck('name')->toArray(),
                'career' => $nanny->careers->pluck('name')->toArray(),
                'zone' => optional($nanny->addresses->first())->zone ?? 'Desconocida',
                'availability' => (bool) $nanny->availability,
            ];
        })->toArray();

        $payload = ['nannies' => $formatted];
        $flaskUrl = config('services.flask.url') . '/api/nannies';

        $response = Http::withHeaders([
            'x-api-key' => config('services.flask.nanny_api_key')  // <- coincide con Flask
        ])->post($flaskUrl, $payload);


        return [
            'sent_count' => count($formatted),
            'flask_response' => $response->json(),
            'status' => $response->status(),
        ];
    }

    /**
     * Envía filtros a Flask para obtener niñeras compatibles
     */
  public function sendFiltersToFlask($filters)
    {
        $payload = $filters;
        $flaskUrl = config('services.flask.url') . '/api/nannies/filter';

        $response = Http::withHeaders([
            'x-api-key' => config('services.flask.nanny_api_key')
        ])->post($flaskUrl, $payload);

        $json = $response->json() ?? [];

        // Si viene anidado dentro de filter_response, lo "subimos" un nivel
        if (isset($json['filter_response'])) {
            $json = $json['filter_response'];
        }

        // Normalizar nombres de clave por seguridad
        if (isset($json['nannies']) && !isset($json['nanny_ids'])) {
            $json['nanny_ids'] = $json['nannies'];
        } elseif (isset($json['ids']) && !isset($json['nanny_ids'])) {
            $json['nanny_ids'] = $json['ids'];
        }

        return [
            'filters_sent' => $filters,
            'flask_response' => $json,
            'status' => $response->status(),
        ];
    }


}
