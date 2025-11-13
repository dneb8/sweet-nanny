<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\BookingAppointment;
use App\Services\NannySearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckNannyAvailabilityJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected Booking $booking;
    protected BookingAppointment $appointment;
    protected NannySearchService $service;

    public function __construct(Booking $booking, BookingAppointment $appointment, NannySearchService $service)
    {
        $this->booking = $booking;
        $this->appointment = $appointment;
        $this->service = $service;
    }

    /**
     * Ejecuta el Job
     */
    public function handle(): void
    {
        // 1) Obtener filtros desde booking/appointment
        $filters = [
            'qualities' => $this->booking->qualities ?? [],
            'courses'   => $this->booking->courses ?? [],
            'career'    => $this->booking->careers ?? [],
            'zone'      => $this->appointment->addresses->first()->zone ?? null,
            'availability' => true,
        ];

        // 2) Llamar a Flask vía Service
        $response = $this->service->sendFiltersToFlask($filters);

        $nannyIds = collect($response['flask_response']['nanny_ids'] ?? []);

        // 3) Guardar los IDs en la cita (opcional) o emitir un evento
        // Por ejemplo: $this->appointment->api_nanny_ids = $nannyIds->toArray();
        // $this->appointment->save();

        // NOTA: No devolver directamente nada
    }
}
