<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Services\AddressService;

class BookingService
{
    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function create(array $payload): Booking
    {
        return DB::transaction(function () use ($payload) {
            $bookingData = data_get($payload, 'booking', []);
            $appointments = data_get($payload, 'appointments', []);
            $addressData  = data_get($payload, 'address');

            // Crea booking primero
            $addressId = data_get($bookingData, 'address_id');
            
            $booking = Booking::create([
                'tutor_id'   => (int) data_get($bookingData, 'tutor_id'),
                'address_id' => $addressId ? (int) $addressId : null, // legacy support
                'description'=> (string) data_get($bookingData, 'description', ''),
                'recurrent'  => (bool) data_get($bookingData, 'recurrent', false),
                'qualities'  => data_get($bookingData, 'qualities', []),
                'degree'     => data_get($bookingData, 'degree'),
                'courses'    => data_get($bookingData, 'courses', []),
            ]);

            // Handle polymorphic address
            if (!$addressId && $addressData) {
                // Create new polymorphic address for this booking
                $this->addressService->createForOwner($addressData, $booking);
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

            return $booking->fresh(['children','bookingAppointments','address','addressPolymorphic']);
        });
    }

    public function update(Booking $booking, array $payload): Booking
    {
        return DB::transaction(function () use ($booking, $payload) {
            $bookingData = $payload['booking'] ?? [];
            $appointments = $payload['appointments'] ?? [];
            $addressData  = $payload['address'] ?? null;

            // Update booking fields
            $booking->update([
                'tutor_id'   => (int) ($bookingData['tutor_id'] ?? $booking->tutor_id),
                'address_id' => $bookingData['address_id'] ?? $booking->address_id, // legacy
                'description'=> $bookingData['description'] ?? $booking->description,
                'recurrent'  => (bool) ($bookingData['recurrent'] ?? $booking->recurrent),
                'qualities'  => $bookingData['qualities'] ?? $booking->qualities,
                'degree'     => $bookingData['degree'] ?? $booking->degree,
                'courses'    => $bookingData['courses'] ?? $booking->courses,
            ]);

            // Handle polymorphic address
            $addressId = $bookingData['address_id'] ?? null;
            
            if (!$addressId && $addressData) {
                // Check if booking already has a polymorphic address
                if ($booking->addressPolymorphic) {
                    $this->addressService->updateWithData($booking->addressPolymorphic, $addressData);
                } else {
                    // Create new polymorphic address
                    $this->addressService->createForOwner($addressData, $booking);
                }
            } elseif ($addressId) {
                // Legacy: address_id is set
                $booking->address_id = $addressId;
                $booking->save();
            }

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

            return $booking->fresh(['children', 'bookingAppointments', 'address', 'addressPolymorphic']);
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
