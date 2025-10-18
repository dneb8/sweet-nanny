<?php

namespace App\Services;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Str;

class AddressService
{
    /**
     * Crea una dirección con relación polimórfica
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->safe();

        // Get polymorphic owner info
        $ownerType = $request->input('owner_type');
        $ownerId = $request->input('owner_id');
        
        $address = Address::create([
            'postal_code' => $validated->postal_code,
            'street' => $validated->street,
            'neighborhood' => $validated->neighborhood,
            'type' => $validated->type,
            'other_type' => $validated->other_type ?? null,
            'internal_number' => $validated->internal_number ?? null,
            'addressable_type' => $ownerType,
            'addressable_id' => $ownerId,
        ]);

        // Backwards compatibility: handle old user association if needed
        $userId = $request->tutor_id ?? $request->nanny_id;
        if ($userId && !$ownerType) {
            $user = User::find($userId);
            if ($user) {
                $user->address()->associate($address);
                $user->save();
            }
        }

        return $address;
    }

    /**
     * Actualiza una dirección existente
     */
    public function updateAddress(Address $address, UpdateAddressRequest $request): Address
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
        
        return $address->fresh();
    }

    /**
     * Elimina una dirección
     */
    public function deleteAddress(Address $address): void
    {
        $address->delete();
    }
}
