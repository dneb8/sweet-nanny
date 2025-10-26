<?php

namespace App\Services;

use App\Classes\Fetcher\Fetcher;
use App\Models\Booking;
use App\Models\Address;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BookingService
{
    /**
     * Obtener todos los bookings en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {
        $bookings = Booking::query()
            ->with([
                'tutor',
                'tutor.user',
                'address',
                'children',
                'bookingAppointments',
                'bookingAppointments.nanny',
                'bookingAppointments.nanny.user',
            ])
            ->orderBy('created_at', 'desc');

        $sortables = ['created_at', 'description', 'status'];
        $searchables = ['description'];

        return Fetcher::for($bookings)
            ->allowFilters([
                'recurrent' => [
                    'using' => function ($filter) {
                        return $filter->transform(fn($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
                    },
                ],
                'status' => [],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(12);
    }

    public function create(array $payload): Booking
    {
        return DB::transaction(function () use ($payload) {
            $bookingData = data_get($payload, 'booking', []);
            $appointments = data_get($payload, 'appointments', []);

            // Handle address_id - store reference to tutor's address
            $addressId = data_get($bookingData, 'address_id');
            
            // Validate that address belongs to the tutor
            if ($addressId) {
                $tutorId = (int) data_get($bookingData, 'tutor_id');
                $address = Address::where('id', $addressId)
                    ->where('addressable_type', 'App\\Models\\Tutor')
                    ->where('addressable_id', $tutorId)
                    ->first();
                
                if (!$address) {
                    throw new \Exception('Invalid address_id: Address does not belong to tutor');
                }
            }
            
            // Create booking with address_id reference
            $booking = Booking::create([
                'tutor_id'    => (int) data_get($bookingData, 'tutor_id'),
                'address_id'  => $addressId, // Store reference to tutor's address
                'description' => (string) data_get($bookingData, 'description', ''),
                'recurrent'   => (bool) data_get($bookingData, 'recurrent', false),
                'qualities'   => data_get($bookingData, 'qualities', []),
                'careers'     => data_get($bookingData, 'careers', []),
                'courses'     => data_get($bookingData, 'courses', []),
            ]);

            // Children: acepta booking.children (preferido) o booking.child_ids
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
            
            // Handle address_id - validate and update
            if (isset($bookingData['address_id'])) {
                $addressId = $bookingData['address_id'];
                
                // Validate that address belongs to the tutor
                if ($addressId) {
                    $tutorId = (int) ($bookingData['tutor_id'] ?? $booking->tutor_id);
                    $address = Address::where('id', $addressId)
                        ->where('addressable_type', 'App\\Models\\Tutor')
                        ->where('addressable_id', $tutorId)
                        ->first();
                    
                    if (!$address) {
                        throw new \Exception('address_id inválido: la dirección no pertenece al tutor');
                    }
                }
            }

            // Update booking (including address_id if provided)
            $booking->update([
                'tutor_id'    => (int) ($bookingData['tutor_id'] ?? $booking->tutor_id),
                'address_id'  => $bookingData['address_id'] ?? $booking->address_id,
                'description' => $bookingData['description'] ?? $booking->description,
                'recurrent'   => (bool) ($bookingData['recurrent'] ?? $booking->recurrent),
                'qualities'   => $bookingData['qualities'] ?? $booking->qualities,
                'careers'     => $bookingData['careers'] ?? $booking->careers,
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
