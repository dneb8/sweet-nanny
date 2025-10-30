<?php

namespace App\Services;

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
     * Crea una dirección con relación polimórfica
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->validated(); // ya normaliza addressable_* en el FormRequest

        $data = [
            'postal_code' => $validated['postal_code'],
            'street' => $validated['street'],
            'neighborhood' => $validated['neighborhood'],
            'type' => $validated['type'],
            'other_type' => $validated['other_type'] ?? null,
            'internal_number' => $validated['internal_number'] ?? null,
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
            'type' => $validated['type'] ?? $address->type,
            'other_type' => array_key_exists('other_type', $validated) ? $validated['other_type'] : $address->other_type,
            'internal_number' => array_key_exists('internal_number', $validated) ? $validated['internal_number'] : $address->internal_number,
        ];

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
