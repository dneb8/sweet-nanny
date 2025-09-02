<?php

namespace App\Services;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Support\Str;

class AddressService
{
    /**
     * Crea una dirección
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->safe();

        $address = Address::create([
            'ulid' => Str::ulid(),
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
     * Actualiza una dirección existente
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
     * Elimina una dirección
     */
    public function deleteAddress(Address $address): void
    {
        $address->delete();
    }
}
