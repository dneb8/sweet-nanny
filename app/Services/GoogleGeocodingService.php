<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocodingService
{
    private string $apiKey;

    private string $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.server_key', '');
    }

    /**
     * Geocode an address string to get detailed address components
     *
     * @param  string  $address
     * @return array|null Returns parsed address data or null on failure
     */
    public function geocodeAddress(string $address): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Maps Server API key is not configured');

            return null;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'address' => $address,
                'key' => $this->apiKey,
                'language' => 'es-MX',
                'region' => 'MX',
            ]);

            if (! $response->successful()) {
                Log::error('Google Geocoding API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if (empty($data['results'])) {
                return null;
            }

            $result = $data['results'][0];

            return $this->parseAddressComponents($result['address_components'], $result['geometry']);
        } catch (\Exception $e) {
            Log::error('Google Geocoding error', [
                'message' => $e->getMessage(),
                'address' => $address,
            ]);

            return null;
        }
    }

    /**
     * Reverse geocode coordinates to get address
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Maps Server API key is not configured');

            return null;
        }

        try {
            $response = Http::get($this->baseUrl, [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $this->apiKey,
                'language' => 'es-MX',
                'region' => 'MX',
            ]);

            if (! $response->successful()) {
                Log::error('Google Reverse Geocoding API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if (empty($data['results'])) {
                return null;
            }

            $result = $data['results'][0];

            return $this->parseAddressComponents($result['address_components'], $result['geometry']);
        } catch (\Exception $e) {
            Log::error('Google Reverse Geocoding error', [
                'message' => $e->getMessage(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return null;
        }
    }

    /**
     * Parse Google address_components into structured data
     *
     * @param  array  $components
     * @param  array  $geometry
     * @return array
     */
    private function parseAddressComponents(array $components, array $geometry): array
    {
        $parsed = [
            'street' => '',
            'external_number' => '',
            'neighborhood' => '',
            'municipality' => '',
            'state' => '',
            'postal_code' => '',
            'latitude' => $geometry['location']['lat'] ?? null,
            'longitude' => $geometry['location']['lng'] ?? null,
        ];

        foreach ($components as $component) {
            $types = $component['types'];
            $longName = $component['long_name'];

            if (in_array('street_number', $types)) {
                $parsed['external_number'] = $longName;
            }
            if (in_array('route', $types)) {
                $parsed['street'] = $longName;
            }
            if (in_array('sublocality_level_1', $types) || in_array('neighborhood', $types)) {
                $parsed['neighborhood'] = $longName;
            }
            if (in_array('locality', $types) || in_array('administrative_area_level_2', $types)) {
                $parsed['municipality'] = $longName;
            }
            if (in_array('administrative_area_level_1', $types)) {
                $parsed['state'] = $longName;
            }
            if (in_array('postal_code', $types)) {
                $parsed['postal_code'] = $longName;
            }
        }

        return $parsed;
    }
}
