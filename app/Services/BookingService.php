<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Booking;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $payload): Booking
    {
        return DB::transaction(function () use ($payload) {
            $bookingData = data_get($payload, 'booking', []);
            $appointments = data_get($payload, 'appointments', []);

            // Handle address_id - validate it belongs to the tutor
            $addressId = data_get($bookingData, 'address_id');
            if ($addressId) {
                $tutorId = (int) data_get($bookingData, 'tutor_id');
                $address = Address::where('id', $addressId)
                    ->where('addressable_type', 'App\\Models\\Tutor')
                    ->where('addressable_id', $tutorId)
                    ->first();

                if (! $address) {
                    throw new \Exception('Invalid address_id: Address does not belong to tutor');
                }
            }

            // Create booking (header metadata only - no address or children)
            $booking = Booking::create([
                'tutor_id' => (int) data_get($bookingData, 'tutor_id'),
                'description' => (string) data_get($bookingData, 'description', ''),
                'recurrent' => (bool) data_get($bookingData, 'recurrent', false),
                'qualities' => data_get($bookingData, 'qualities', []),
                'careers' => data_get($bookingData, 'careers', []),
                'courses' => data_get($bookingData, 'courses', []),
            ]);

            // Extract children IDs from booking data
            $rawChildren = data_get($bookingData, 'child_ids', data_get($bookingData, 'children', []));
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
                ->filter()   // Remove 0 / null
                ->unique()
                ->values()
                ->all();

            // Create appointments and apply address + children to each
            foreach ($appointments as $a) {
                $appointment = $booking->bookingAppointments()->create([
                    'start_date' => Carbon::parse(data_get($a, 'start_date')),
                    'end_date' => Carbon::parse(data_get($a, 'end_date')),
                    'duration' => (int) data_get($a, 'duration', 0),
                    'status' => (string) data_get($a, 'status', 'draft'),
                    'payment_status' => (string) data_get($a, 'payment_status', 'unpaid'),
                    'extra_hours' => (int) data_get($a, 'extra_hours', 0),
                    'total_cost' => (float) data_get($a, 'total_cost', 0),
                ]);

                // Apply address to this appointment
                if ($addressId) {
                    $appointment->addresses()->sync([$addressId]);
                }

                // Apply children to this appointment
                if (! empty($childIds)) {
                    $appointment->children()->sync($childIds);
                }
            }

            return $booking->fresh(['bookingAppointments.addresses', 'bookingAppointments.children']);
        });
    }

    public function update(Booking $booking, array $payload): Booking
    {
        return DB::transaction(function () use ($booking, $payload) {
            $bookingData = $payload['booking'] ?? [];
            $appointments = $payload['appointments'] ?? [];

            // Handle address_id - validate it belongs to the tutor
            $addressId = $bookingData['address_id'] ?? null;
            if ($addressId) {
                $tutorId = (int) ($bookingData['tutor_id'] ?? $booking->tutor_id);
                $address = Address::where('id', $addressId)
                    ->where('addressable_type', 'App\\Models\\Tutor')
                    ->where('addressable_id', $tutorId)
                    ->first();

                if (! $address) {
                    throw new \Exception('address_id inválido: la dirección no pertenece al tutor');
                }
            }

            // Update booking (header metadata only - no address or children)
            $booking->update([
                'tutor_id' => (int) ($bookingData['tutor_id'] ?? $booking->tutor_id),
                'description' => $bookingData['description'] ?? $booking->description,
                'recurrent' => (bool) ($bookingData['recurrent'] ?? $booking->recurrent),
                'qualities' => $bookingData['qualities'] ?? $booking->qualities,
                'careers' => $bookingData['careers'] ?? $booking->careers,
                'courses' => $bookingData['courses'] ?? $booking->courses,
            ]);

            // Extract children IDs from booking data
            $childIds = array_map('intval', $bookingData['child_ids'] ?? []);

            // Delete existing appointments (they will be recreated with new address/children)
            $booking->bookingAppointments()->delete();

            // Recreate appointments and apply address + children to each
            foreach ($appointments as $a) {
                $appointment = $booking->bookingAppointments()->create([
                    'start_date' => Carbon::parse($a['start_date']),
                    'end_date' => Carbon::parse($a['end_date']),
                    'duration' => (int) ($a['duration'] ?? 0),
                    'status' => Arr::get($a, 'status', 'pending'),
                    'payment_status' => Arr::get($a, 'payment_status', 'unpaid'),
                    'extra_hours' => (int) Arr::get($a, 'extra_hours', 0),
                    'total_cost' => (float) Arr::get($a, 'total_cost', 0),
                ]);

                // Apply address to this appointment
                if ($addressId) {
                    $appointment->addresses()->sync([$addressId]);
                }

                // Apply children to this appointment
                if (! empty($childIds)) {
                    $appointment->children()->sync($childIds);
                }
            }

            return $booking->fresh(['bookingAppointments.addresses', 'bookingAppointments.children']);
        });
    }

    public function delete(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            // Delete appointments (cascade will handle children and addresses via pivot tables)
            $booking->bookingAppointments()->delete();
            $booking->delete();
        });
    }
}
