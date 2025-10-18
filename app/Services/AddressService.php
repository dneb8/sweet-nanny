<?php

namespace App\Services;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AddressService
{
    /**
     * Create an address for a polymorphic owner
     * 
     * @param array $data Address data
     * @param Model $owner The owner model (Tutor, Nanny, or Booking)
     */
    public function createForOwner(array $data, Model $owner): Address
    {
        $address = new Address([
            'postal_code' => $data['postal_code'],
            'street' => $data['street'],
            'neighborhood' => $data['neighborhood'],
            'type' => $data['type'],
            'other_type' => $data['other_type'] ?? null,
            'internal_number' => $data['internal_number'] ?? null,
        ]);

        $owner->addresses()->save($address);

        return $address;
    }

    /**
     * Get all addresses for a polymorphic owner
     */
    public function getForOwner(Model $owner)
    {
        return $owner->addresses ?? collect();
    }

    /**
     * Crea una dirección (legacy support)
     */
    public function createAddress(CreateAddressRequest $request): Address
    {
        $validated = $request->safe();

        $address = Address::create([
            'postal_code' => $validated->postal_code,
            'street' => $validated->street,
            'neighborhood' => $validated->neighborhood,
            'type' => $validated->type,
            'other_type' => $validated->other_type ?? null,
            'internal_number' => $validated->internal_number ?? null,
        ]);

        $userId = $request->tutor_id ?? $request->nanny_id;

        if ($userId) {
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
     * Update an address with raw data
     */
    public function updateWithData(Address $address, array $data): Address
    {
        $address->update([
            'postal_code' => $data['postal_code'] ?? $address->postal_code,
            'street' => $data['street'] ?? $address->street,
            'neighborhood' => $data['neighborhood'] ?? $address->neighborhood,
            'type' => $data['type'] ?? $address->type,
            'other_type' => $data['other_type'] ?? $address->other_type,
            'internal_number' => $data['internal_number'] ?? $address->internal_number,
        ]);

        return $address;
    }

    /**
     * Elimina una dirección
     */
    public function deleteAddress(Address $address): void
    {
        $address->delete();
    }
}
