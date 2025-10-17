<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BookingService
{
    public function create(array $payload): Booking
    {
        return DB::transaction(function () use ($payload) {
            $bookingData = data_get($payload, 'booking', []);
            $appointments = data_get($payload, 'appointments', []);
            $addressData  = data_get($payload, 'address');

            // Dirección opcional: crea si no se mandó address_id
            $addressId = data_get($bookingData, 'address_id');
            if (!$addressId && $addressData) {
                $address = Address::create([
                    'tutor_id'        => (int) data_get($bookingData, 'tutor_id'),
                    'postal_code'     => (string) data_get($addressData, 'postal_code', ''),
                    'street'          => (string) data_get($addressData, 'street', ''),
                    'neighborhood'    => (string) data_get($addressData, 'neighborhood', ''),
                    'type'            => data_get($addressData, 'type'),
                    'other_type'      => data_get($addressData, 'other_type'),
                    'internal_number' => data_get($addressData, 'internal_number'),
                ]);
                $addressId = $address->id;
            }

            // Crea booking
            $booking = Booking::create([
                'tutor_id'   => (int) data_get($bookingData, 'tutor_id'),
                'address_id' => $addressId,
                'description'=> (string) data_get($bookingData, 'description', ''),
                'recurrent'  => (bool) data_get($bookingData, 'recurrent', false),
            ]);

            // Children: acepta booking.children (preferido) o booking.child_ids
            $rawChildren = data_get($bookingData, 'children', data_get($bookingData, 'child_ids', []));
            $childIds = collect($rawChildren)
                ->map(function ($c) {
                    if (is_array($c)) {
                        return (int) data_get($c, 'id', 0);
                    }
                    if (is_object($c)) {
                        return (int) data_get($c, 'id', 0);
                    }
                    return (int) $c;
                })
                ->filter()   // quita 0 / null
                ->unique()
                ->values()
                ->all();

            if (!empty($childIds)) {
                $booking->children()->sync($childIds);
            }

            // Appointments
            $rows = [];
            foreach ($appointments as $a) {
                $rows[] = [
                    'start_date'     => Carbon::parse(data_get($a, 'start_date')),
                    'end_date'       => Carbon::parse(data_get($a, 'end_date')),
                    'duration'       => (int) data_get($a, 'duration', 0),
                    'status'         => (string) data_get($a, 'status', 'pending'),
                    'payment_status' => (string) data_get($a, 'payment_status', 'unpaid'),
                    'extra_hours'    => (int) data_get($a, 'extra_hours', 0),
                    'total_cost'     => (float) data_get($a, 'total_cost', 0),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            if ($rows) {
                $booking->bookingAppointments()->createMany($rows);
            }

            return $booking->fresh(['children','bookingAppointments','address']);
        });
    }

    public function update(Booking $booking, array $payload): Booking
    {
        return DB::transaction(function () use ($booking, $payload) {
            $bookingData = $payload['booking'] ?? [];
            $appointments = $payload['appointments'] ?? [];
            $addressData  = $payload['address'] ?? null;

            // - Si viene explícito en el payload, se respeta (permitiendo null para limpiar).
            //  - Si NO viene, conservamos el address_id actual.
            if (array_key_exists('address_id', $bookingData)) {
                $addressId = $bookingData['address_id'] ? (int) $bookingData['address_id'] : null;
            } else {
                $addressId = $booking->address_id;
            }

            // 2) Crear/actualizar dirección inline si NO hay address_id pero SÍ vienen datos de address.
            if (!$addressId && $addressData) {
                if ($booking->address) {
                    $booking->address->update([
                        'tutor_id'        => $bookingData['tutor_id'] ?? $booking->tutor_id,
                        'postal_code'     => $addressData['postal_code']     ?? $booking->address->postal_code,
                        'street'          => $addressData['street']          ?? $booking->address->street,
                        'neighborhood'    => $addressData['neighborhood']    ?? $booking->address->neighborhood,
                        'type'            => $addressData['type']            ?? $booking->address->type,
                        'other_type'      => $addressData['other_type']      ?? $booking->address->other_type,
                        'internal_number' => $addressData['internal_number'] ?? $booking->address->internal_number,
                    ]);
                    $addressId = $booking->address->id;
                } else {
                    $address = Address::create([
                        'tutor_id'        => $bookingData['tutor_id'] ?? $booking->tutor_id,
                        'postal_code'     => $addressData['postal_code']     ?? '',
                        'street'          => $addressData['street']          ?? '',
                        'neighborhood'    => $addressData['neighborhood']    ?? '',
                        'type'            => $addressData['type']            ?? null,
                        'other_type'      => $addressData['other_type']      ?? null,
                        'internal_number' => $addressData['internal_number'] ?? null,
                    ]);
                    $addressId = $address->id;
                }
            }

            // 3) Actualizar booking
            $booking->update([
                'tutor_id'   => (int) ($bookingData['tutor_id'] ?? $booking->tutor_id),
                'address_id' => $addressId,
                'description'=> $bookingData['description'] ?? $booking->description,
                'recurrent'  => (bool) ($bookingData['recurrent'] ?? $booking->recurrent),
            ]);

            // 4) Sincronizar niños (admite strings o ints)
            $childIds = array_map('intval', $bookingData['child_ids'] ?? []);
            $booking->children()->sync($childIds);

            // 5) Reemplazar citas:
            //    Usa SIEMPRE la relación correcta 'bookingAppointments' (igual que en create()).
            $booking->bookingAppointments()->delete();

            $rows = array_map(function ($a) {
                return [
                    'start_date'     => Carbon::parse($a['start_date']),
                    'end_date'       => Carbon::parse($a['end_date']),
                    'duration'       => (int) ($a['duration'] ?? 0),
                    'status'         => Arr::get($a, 'status', 'pending'),
                    'payment_status' => Arr::get($a, 'payment_status', 'unpaid'),
                    'extra_hours'    => (int) Arr::get($a, 'extra_hours', 0),
                    'total_cost'     => (float) Arr::get($a, 'total_cost', 0),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }, $appointments);

            if (!empty($rows)) {
                $booking->bookingAppointments()->createMany($rows);
            }

            return $booking->fresh(['children', 'bookingAppointments', 'address']);
        });
    }


    public function delete(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->bookingAppointments()->delete();
            $booking->children()->detach();
            $booking->delete();
        });
    }
}
