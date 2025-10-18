<?php

namespace App\Http\Controllers;

use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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

    /**
     * Lista direcciones para un propietario polimórfico
     * Ruta sugerida: GET /addresses/{ownerType}/{ownerId}
     * ownerType puede ser "Tutor" | "Nanny" | "Booking" o FQCN.
     */
    public function index(string $ownerType, int $ownerId): JsonResponse
    {
        $modelClass = $this->normalizeOwnerFqcn($ownerType);

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Tipo de propietario inválido.'], 400);
        }

        $addresses = Address::query()
            ->where('addressable_type', $modelClass)
            ->where('addressable_id', $ownerId)
            ->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(AddressService $addressService, CreateAddressRequest $request)
    {
        $address = $addressService->createAddress($request);

        // Peticiones Inertia => REDIRECT + FLASH
        if ($request->hasHeader('X-Inertia')) {
            return back(303)->with([
                'success' => 'Dirección creada correctamente.',
                'recent'  => ['address' => $address->toArray()],
            ]);
        }

        // Solo para llamadas API reales (sin Inertia)
        if ($request->expectsJson()) {
            return response()->json([
                'address' => $address,
                'message' => 'Dirección creada correctamente.',
            ], 201);
        }

        // Fallback web normal
        return back()->with('success', 'Dirección creada correctamente.');
    }

    public function update(AddressService $addressService, UpdateAddressRequest $request, Address $address)
    {
        $updated = $addressService->updateAddress($address, $request);

        if ($request->hasHeader('X-Inertia')) {
            return back(303)->with([
                'success' => 'Dirección actualizada correctamente.',
                'recent'  => ['address' => $updated->toArray()],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'address' => $updated,
                'message' => 'Dirección actualizada correctamente.',
            ]);
        }

        return back()->with('success', 'Dirección actualizada correctamente.');
    }


    /**
     * Elimina una dirección
     */
    public function destroy(Address $address): RedirectResponse
    {
        $address->delete();

        return redirect()->back()->with('message', [
            'title'       => 'Dirección eliminada',
            'description' => 'La dirección ha sido eliminada correctamente.',
        ]);
    }
}
