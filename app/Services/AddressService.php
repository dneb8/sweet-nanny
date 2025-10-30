<?php

namespace App\Services;

use App\Enums\Address\ZoneEnum;
use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AddressService
{
    /**
     * Normaliza string a FQCN dentro de App\Models\
     */
    private function normalizeOwnerFqcn(string $type): string
    {
        $type = str_replace('/', '\\', trim($type));
        if (str_contains($type, '\\')) {
            return $type;
        }

        return 'App\\Models\\'.Str::studly($type);
    }

    /**
     * Resuelve el owner desde el request validado.
     */
    private function resolveOwner(array $data): ?Model
    {
        $type = $data['addressable_type'] ?? null;
        $id = $data['addressable_id'] ?? null;

        if (! $type || ! $id) {
            return null;
        }

        $fqcn = $this->normalizeOwnerFqcn($type);

        if (! class_exists($fqcn)) {
            return null;
        }

        /** @var \Illuminate\Database\Eloquent\Model|null $owner */
        $owner = $fqcn::query()->find($id);

        return $owner;
    }

    /**
     * Determina la zona (ZoneEnum) según el código postal
     * Removido el lanzamiento de excepción para códigos postales fuera de rango
     * AWS Location Service validará la dirección completa
     */
    private function determineZoneFromPostalCode(int $cp): ?ZoneEnum
    {
        return match (true) {
            $cp >= 44000 && $cp <= 45000 => ZoneEnum::GUADALAJARA,
            $cp >= 45000 && $cp <= 45246 => ZoneEnum::ZAPOPAN,
            $cp >= 45500 && $cp <= 45640 => ZoneEnum::TLAQUEPAQUE,
            $cp >= 45640 && $cp <= 45680 => ZoneEnum::TLAJOMULCO,
            $cp >= 45400 && $cp <= 45430 => ZoneEnum::TONALA,
            default => null, // Permitir otros códigos postales validados por AWS Location
        };
    }

    /**
     * Crea una dirección con relación polimórfica
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->validated(); // ya normaliza addressable_* en el FormRequest

        $cp = (int) $validated['postal_code'];
        $zone = $this->determineZoneFromPostalCode($cp);

        $data = [
            'postal_code' => $validated['postal_code'],
            'street' => $validated['street'],
            'neighborhood' => $validated['neighborhood'],
            'external_number' => $validated['external_number'],
            'internal_number' => $validated['internal_number'] ?? null,
            'municipality' => $validated['municipality'] ?? null,
            'state' => $validated['state'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'type' => $validated['type'],
            'zone' => $zone?->value, // se asigna automáticamente si está en rango
        ];

        // Intentamos crear por la relación morphMany addresses()
        if ($owner = $this->resolveOwner($validated)) {
            if (method_exists($owner, 'addresses')) {
                /** @var Address $address */
                $address = $owner->addresses()->create($data);

                return $address->fresh();
            }
        }

        // Fallback: crear con addressable_* directo
        /** @var Address $address */
        $address = Address::create(array_merge($data, [
            'addressable_type' => $this->normalizeOwnerFqcn($validated['addressable_type']),
            'addressable_id' => $validated['addressable_id'],
        ]));

        return $address->fresh();
    }

    /**
     * Actualiza una dirección existente (y si vienen addressable_*, reasocia)
     */
    public function updateAddress(Address $address, UpdateAddressRequest $request): Address
    {
        $validated = $request->validated();

        $payload = [
            'postal_code' => $validated['postal_code'] ?? $address->postal_code,
            'street' => $validated['street'] ?? $address->street,
            'neighborhood' => $validated['neighborhood'] ?? $address->neighborhood,
            'external_number' => $validated['external_number'] ?? $address->external_number,
            'internal_number' => array_key_exists('internal_number', $validated) ? $validated['internal_number'] : $address->internal_number,
            'municipality' => array_key_exists('municipality', $validated) ? $validated['municipality'] : $address->municipality,
            'state' => array_key_exists('state', $validated) ? $validated['state'] : $address->state,
            'latitude' => array_key_exists('latitude', $validated) ? $validated['latitude'] : $address->latitude,
            'longitude' => array_key_exists('longitude', $validated) ? $validated['longitude'] : $address->longitude,
            'type' => $validated['type'] ?? $address->type,
        ];

        // Si cambia el CP, recalculamos la zona
        if (! empty($validated['postal_code'])) {
            $cp = (int) $validated['postal_code'];
            $zone = $this->determineZoneFromPostalCode($cp);
            $payload['zone'] = $zone?->value;
        }

        // Si quieren cambiar el owner, permitimos reasociación
        $hasOwnerType = ! empty($validated['addressable_type']);
        $hasOwnerId = ! empty($validated['addressable_id']);

        if ($hasOwnerType && $hasOwnerId) {
            $newOwner = $this->resolveOwner($validated);
            if ($newOwner) {
                $address->addressable()->associate($newOwner);
            }
        }

        $address->fill($payload)->save();

        return $address->fresh();
    }
}
