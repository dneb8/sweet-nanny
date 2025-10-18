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

            // Crea booking primero (sin address_id)
            $booking = Booking::create([
                'tutor_id'    => (int) data_get($bookingData, 'tutor_id'),
                'description' => (string) data_get($bookingData, 'description', ''),
                'recurrent'   => (bool) data_get($bookingData, 'recurrent', false),
                'qualities'   => data_get($bookingData, 'qualities', []),
                'degree'      => data_get($bookingData, 'degree'),
                'courses'     => data_get($bookingData, 'courses', []),
            ]);
            
            // Handle address - either select existing or create new (polymorphic)
            $addressId = data_get($bookingData, 'address_id');
            if ($addressId) {
                // Using existing address - associate it polymorphically
                $existingAddress = Address::find($addressId);
                if ($existingAddress) {
                    // Update to be owned by this booking
                    $existingAddress->update([
                        'addressable_type' => 'App\\Models\\Booking',
                        'addressable_id' => $booking->id,
                    ]);
                }
            } elseif ($addressData) {
                // Create new address owned by booking
                Address::create([
                    'postal_code'     => (string) data_get($addressData, 'postal_code', ''),
                    'street'          => (string) data_get($addressData, 'street', ''),
                    'neighborhood'    => (string) data_get($addressData, 'neighborhood', ''),
                    'type'            => data_get($addressData, 'type'),
                    'other_type'      => data_get($addressData, 'other_type'),
                    'internal_number' => data_get($addressData, 'internal_number'),
                    'addressable_type' => 'App\\Models\\Booking',
                    'addressable_id'   => $booking->id,
                ]);
            }

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

            // Handle address polymorphically
            $addressId = $bookingData['address_id'] ?? null;
            
            if ($addressId) {
                // Using existing address - check if we need to reassociate
                $existingAddress = Address::find($addressId);
                if ($existingAddress) {
                    // If this address isn't already owned by this booking, update it
                    if ($existingAddress->addressable_type !== 'App\\Models\\Booking' || 
                        $existingAddress->addressable_id !== $booking->id) {
                        $existingAddress->update([
                            'addressable_type' => 'App\\Models\\Booking',
                            'addressable_id' => $booking->id,
                        ]);
                    }
                }
            } elseif ($addressData) {
                // Create or update address inline
                if ($booking->address) {
                    $booking->address->update([
                        'postal_code'     => $addressData['postal_code']     ?? $booking->address->postal_code,
                        'street'          => $addressData['street']          ?? $booking->address->street,
                        'neighborhood'    => $addressData['neighborhood']    ?? $booking->address->neighborhood,
                        'type'            => $addressData['type']            ?? $booking->address->type,
                        'other_type'      => $addressData['other_type']      ?? $booking->address->other_type,
                        'internal_number' => $addressData['internal_number'] ?? $booking->address->internal_number,
                    ]);
                } else {
                    Address::create([
                        'postal_code'     => $addressData['postal_code']     ?? '',
                        'street'          => $addressData['street']          ?? '',
                        'neighborhood'    => $addressData['neighborhood']    ?? '',
                        'type'            => $addressData['type']            ?? null,
                        'other_type'      => $addressData['other_type']      ?? null,
                        'internal_number' => $addressData['internal_number'] ?? null,
                        'addressable_type' => 'App\\Models\\Booking',
                        'addressable_id'   => $booking->id,
                    ]);
                }
            }

            // Update booking
            $booking->update([
                'tutor_id'    => (int) ($bookingData['tutor_id'] ?? $booking->tutor_id),
                'description' => $bookingData['description'] ?? $booking->description,
                'recurrent'   => (bool) ($bookingData['recurrent'] ?? $booking->recurrent),
                'qualities'   => $bookingData['qualities'] ?? $booking->qualities,
                'degree'      => $bookingData['degree'] ?? $booking->degree,
                'courses'     => $bookingData['courses'] ?? $booking->courses,
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
