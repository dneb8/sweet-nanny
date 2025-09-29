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
            $bookingData = $payload['booking'];
            $appointments = $payload['appointments'] ?? [];
            $addressData  = $payload['address'] ?? null;

            $addressId = $bookingData['address_id'] ?? null;
            if (!$addressId && $addressData) {
                $address = Address::create([
                    'tutor_id'       => $bookingData['tutor_id'],
                    'postal_code'    => $addressData['postal_code'] ?? '',
                    'street'         => $addressData['street'] ?? '',
                    'neighborhood'   => $addressData['neighborhood'] ?? '',
                    'type'           => $addressData['type'] ?? null,
                    'other_type'     => $addressData['other_type'] ?? null,
                    'internal_number'=> $addressData['internal_number'] ?? null,
                ]);
                $addressId = $address->id;
            }

            $booking = Booking::create([
                'tutor_id'   => $bookingData['tutor_id'],
                'address_id' => $addressId,
                'description'=> $bookingData['description'],
                'recurrent'  => (bool) $bookingData['recurrent'],
            ]);

            $childIds = array_map('strval', $bookingData['child_ids'] ?? []);
            if (!empty($childIds)) {
                $booking->children()->sync($childIds);
            }

            $rows = [];
            foreach ($appointments as $a) {
                $rows[] = [
                    'start_date'     => Carbon::parse($a['start_date']),
                    'end_date'       => Carbon::parse($a['end_date']),
                    'duration'       => (int) $a['duration'],
                    'status'         => Arr::get($a, 'status', 'pending'),
                    'payment_status' => Arr::get($a, 'payment_status', 'unpaid'),
                    'extra_hours'    => (int) Arr::get($a, 'extra_hours', 0),
                    'total_cost'     => (float) Arr::get($a, 'total_cost', 0),
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
            $bookingData = $payload['booking'];
            $appointments = $payload['appointments'] ?? [];
            $addressData  = $payload['address'] ?? null;

            $addressId = $bookingData['address_id'] ?? null;
            if (!$addressId && $addressData) {
                if ($booking->address) {
                    $booking->address->update([
                        'postal_code'    => $addressData['postal_code'] ?? $booking->address->postal_code,
                        'street'         => $addressData['street'] ?? $booking->address->street,
                        'neighborhood'   => $addressData['neighborhood'] ?? $booking->address->neighborhood,
                        'type'           => $addressData['type'] ?? $booking->address->type,
                        'other_type'     => $addressData['other_type'] ?? $booking->address->other_type,
                        'internal_number'=> $addressData['internal_number'] ?? $booking->address->internal_number,
                    ]);
                    $addressId = $booking->address->id;
                } else {
                    $address = Address::create([
                        'tutor_id'       => $bookingData['tutor_id'],
                        'postal_code'    => $addressData['postal_code'] ?? '',
                        'street'         => $addressData['street'] ?? '',
                        'neighborhood'   => $addressData['neighborhood'] ?? '',
                        'type'           => $addressData['type'] ?? null,
                        'other_type'     => $addressData['other_type'] ?? null,
                        'internal_number'=> $addressData['internal_number'] ?? null,
                    ]);
                    $addressId = $address->id;
                }
            }

            $booking->update([
                'tutor_id'   => $bookingData['tutor_id'],
                'address_id' => $addressId,
                'description'=> $bookingData['description'],
                'recurrent'  => (bool) $bookingData['recurrent'],
            ]);

            $childIds = array_map('strval', $bookingData['child_ids'] ?? []);
            $booking->children()->sync($childIds);

            $booking->appointments()->delete();
            $rows = [];
            foreach ($appointments as $a) {
                $rows[] = [
                    'start_date'     => Carbon::parse($a['start_date']),
                    'end_date'       => Carbon::parse($a['end_date']),
                    'duration'       => (int) $a['duration'],
                    'status'         => \Illuminate\Support\Arr::get($a, 'status', 'pending'),
                    'payment_status' => \Illuminate\Support\Arr::get($a, 'payment_status', 'unpaid'),
                    'extra_hours'    => (int) \Illuminate\Support\Arr::get($a, 'extra_hours', 0),
                    'total_cost'     => (float) \Illuminate\Support\Arr::get($a, 'total_cost', 0),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            if ($rows) {
                $booking->appointments()->createMany($rows);
            }

            return $booking->fresh(['children','appointments','address']);
        });
    }

    public function delete(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->appointments()->delete();
            $booking->children()->detach();
            $booking->delete();
        });
    }
}
