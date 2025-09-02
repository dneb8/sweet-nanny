<?php

namespace App\Services;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Enums\Address\TypeEnum;

class AddressService
{
    /**
     * Crear una nueva dirección
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->safe(); // Obtiene solo los datos validados

        $address = Address::create([
            'postal_code' => $validated->postal_code,
            'street' => $validated->street,
            'neighborhood' => $validated->neighborhood,
            'type' => $validated->type,
            'other_type' => $validated->other_type ?? null,
            'internal_number' => $validated->internal_number ?? null,
        ]);

        return $address;
    }

    /**
     * Actualizar una dirección existente
     */
    public function updateAddress(Address $address, UpdateAddressRequest $request): void
    {
        $validated = $request->safe();

        $address->update([
            'postal_code' => $validated->postal_code,
            'street' => $validated->street,
            'neighborhood' => $validated->neighborhood,
            'type' => $validated->type,
            'other_type' => $validated->other_type ?? $address->other_type,
            'internal_number' => $validated->internal_number ?? $address->internal_number,
        ]);
    }

    /**
     * Eliminar una dirección
     */
    public function deleteAddress(Address $address): void
    {
        $address->delete(); // Soft delete
    }
}
