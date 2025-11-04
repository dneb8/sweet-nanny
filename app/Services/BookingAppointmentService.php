<?php

namespace App\Services;

use App\Classes\Fetcher\Fetcher;
use App\Models\BookingAppointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class BookingAppointmentService
{
    /**
     * Obtener todos los booking appointments en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {
        $user = Auth::user();

        $appointments = BookingAppointment::query()
            ->with([
                'booking.tutor.user',
                'nanny.user',
                'children',
                'addresses',
            ]);

        // Aplicar filtro de permisos:
        // Si el usuario es Nanny, solo ver sus propias citas
        if ($user && $user->hasRole('nanny')) {
            $appointments->whereHas('nanny', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        // Si es Admin, ve todo (no se aplica filtro adicional)

        $appointments->orderBy('start_date', 'desc');

        $sortables = ['start_date', 'end_date', 'status'];
        $searchables = [];

        return Fetcher::for($appointments)
            ->allowFilters([
                'status' => [
                    'as' => 'status',
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->touch(function ($fetcher) use ($user) {
                // Búsqueda personalizada por tutor o nanny
                $searchTerm = request()->get('searchTerm');
                if ($searchTerm) {
                    $fetcher->query->where(function ($q) use ($searchTerm) {
                        // Buscar en tutor
                        $q->whereHas('booking.tutor.user', function ($query) use ($searchTerm) {
                            $query->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('surnames', 'like', "%{$searchTerm}%");
                        })
                        // Buscar en nanny
                        ->orWhereHas('nanny.user', function ($query) use ($searchTerm) {
                            $query->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('surnames', 'like', "%{$searchTerm}%");
                        });
                    });
                }
            })
            ->paginate(12);
    }
}
