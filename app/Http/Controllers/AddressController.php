<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Normaliza un tipo de modelo a FQCN (App\Models\X)
     */
    private function normalizeOwnerFqcn(string $type): string
    {
        $type = str_replace('/', '\\', trim($type));
        // Si ya parece FQCN, lo regresamos
        if (str_contains($type, '\\')) {
            return $type;
        }
        // Asegura StudlyCase y lo coloca bajo App\Models\
        $studly = \Illuminate\Support\Str::studly($type);
        return "App\\Models\\{$studly}";
    }

    public function store(AddressService $addressService, CreateAddressRequest $request)
    {
        $address = $addressService->createAddress($request);

        if ($request->inertia()) {
            return back(303)->with([
                'success' => 'Dirección creada correctamente.',
                'recent'  => ['address' => $address->toArray()],
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

        if ($request->inertia()) {
            return back(303)->with([
                'success' => 'Dirección actualizada correctamente.',
                'recent'  => ['address' => $updated->toArray()],
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

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('message', [
            'title'       => 'Dirección eliminada',
            'description' => 'La dirección ha sido eliminada correctamente.',
        ]);
    }

}
