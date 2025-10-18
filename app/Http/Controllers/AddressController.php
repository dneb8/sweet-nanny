<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\{CreateAddressRequest, UpdateAddressRequest};
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    /**
     * Lista direcciones para un propietario polimórfico
     */
    public function index(string $ownerType, int $ownerId)
    {
        $modelClass = 'App\\Models\\' . $ownerType;
        
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid owner type'], 400);
        }
        
        $addresses = Address::where('addressable_type', $modelClass)
            ->where('addressable_id', $ownerId)
            ->get();
            
        return response()->json(['addresses' => $addresses]);
    }

    /**
     * Almacena una nueva dirección
     */
    public function store(AddressService $addressService, CreateAddressRequest $request)
    {
        $address = $addressService->createAddress($request);

        // If it's an AJAX request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'address' => $address,
                'message' => 'Dirección creada correctamente.'
            ], 201);
        }

        return redirect()->back()->with('message', [
            'title' => 'Dirección creada',
            'description' => 'La dirección ha sido creada correctamente.',
        ]);
    }

    /**
     * Actualiza una dirección existente
     */
    public function update(AddressService $addressService, UpdateAddressRequest $request, Address $address)
    {
        // Gate::authorize('edit', Address::class);

        $updatedAddress = $addressService->updateAddress($address, $request);

        // If it's an AJAX request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'address' => $updatedAddress,
                'message' => 'Dirección actualizada correctamente.'
            ]);
        }

        return redirect()->back()->with('message', [
            'title' => 'Dirección actualizada',
            'description' => 'La dirección ha sido actualizada correctamente.',
        ]);
    }

    /**
     * Elimina una dirección
     */
    public function destroy(Address $address): RedirectResponse
    {
        // Gate::authorize('delete', Address::class);

        $address->delete();

        return redirect()->back()->with('message', [
            'title' => 'Dirección eliminada',
            'description' => 'La dirección ha sido eliminada correctamente.',
        ]);
    }
}
