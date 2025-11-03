<?php

namespace App\Notifications;

use App\Models\BookingAppointment;
use App\Models\Nanny;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NannyChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public BookingAppointment $appointment,
        public Nanny $oldNanny,
        public Nanny $newNanny
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing('booking');
        $this->oldNanny->loadMissing('user');
        $this->newNanny->loadMissing('user');

        return [
            'message' => sprintf(
                'La niñera del appointment #%d fue cambiada de %s a %s',
                $this->appointment->id,
                $this->oldNanny->user->name ?? 'Desconocida',
                $this->newNanny->user->name ?? 'Desconocida'
            ),
            'appointment_id' => $this->appointment->id,
            'booking_id' => $this->appointment->booking_id,
            'old_nanny_name' => $this->oldNanny->user->name ?? 'Desconocida',
            'new_nanny_name' => $this->newNanny->user->name ?? 'Desconocida',
            'start_date' => $this->appointment->start_date->toISOString(),
            'redirect' => route('bookings.show', $this->appointment->booking_id),
            'type' => 'nanny_changed',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $this->appointment->loadMissing('booking');
        $this->oldNanny->loadMissing('user');
        $this->newNanny->loadMissing('user');

        return new BroadcastMessage([
            'message' => sprintf(
                'La niñera del appointment #%d fue cambiada de %s a %s',
                $this->appointment->id,
                $this->oldNanny->user->name ?? 'Desconocida',
                $this->newNanny->user->name ?? 'Desconocida'
            ),
            'appointment_id' => $this->appointment->id,
            'booking_id' => $this->appointment->booking_id,
            'old_nanny_name' => $this->oldNanny->user->name ?? 'Desconocida',
            'new_nanny_name' => $this->newNanny->user->name ?? 'Desconocida',
            'start_date' => $this->appointment->start_date->toISOString(),
            'redirect' => route('bookings.show', $this->appointment->booking_id),
            'type' => 'nanny_changed',
        ]);
    }
}
