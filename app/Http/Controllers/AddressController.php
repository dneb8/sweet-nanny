<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private function isInertia(Request $request): bool
    {
        // Inertia manda este header; úsalo para decidir la respuesta
        return $request->hasHeader('X-Inertia');
    }

    public function store(AddressService $addressService, CreateAddressRequest $request)
    {
        $address = $addressService->createAddress($request);

        if ($this->isInertia($request)) {
            // SEE OTHER para formularios Inertia
            return back(303)->with([
                'success' => 'Dirección creada correctamente.',
                'recent' => ['address' => $address->toArray()],
            ]);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'address' => $address,
                'message' => 'Dirección creada correctamente.',
            ], 201);
        }

        return back()->with('success', 'Dirección creada correctamente.');
    }

    public function update(AddressService $addressService, UpdateAddressRequest $request, Address $address)
    {
        $updated = $addressService->updateAddress($address, $request);

        if ($this->isInertia($request)) {
            return back(303)->with([
                'success' => 'Dirección actualizada correctamente.',
                'recent' => ['address' => $updated->toArray()],
            ]);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'address' => $updated,
                'message' => 'Dirección actualizada correctamente.',
            ]);
        }

        return back()->with('success', 'Dirección actualizada correctamente.');
    }

    public function destroy(Address $address, Request $request)
    {
        $address->delete();

        if ($this->isInertia($request)) {
            // <- IMPORTANTE: no devolver JSON a Inertia
            return back(303)->with('success', 'Dirección eliminada correctamente.');
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Dirección eliminada correctamente.');
    }
}
