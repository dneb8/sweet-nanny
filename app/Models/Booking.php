<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'address_id',
        'description',
        'recurrent',
        'status',
        'qualities',
        'careers',
        'courses',
    ];

    protected $casts = [
        'qualities' => 'array',
        'careers' => 'array',
        'courses' => 'array',
        'recurrent' => 'boolean',
    ];

    // Relación uno a muchos con BookingAppointment
    public function bookingAppointments()
    {
        return $this->hasMany(BookingAppointment::class);
    }

    // Relación con Tutor (cada booking pertenece a un tutor)
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    // Relación con Address (belongsTo - foreign key reference)
    // Address remains owned by Tutor, booking just references it
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Deprecated: Polymorphic relation (kept for migration)
    public function addressPolymorphic()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    // Relación con Child (cada booking puede tener varios niños)
    public function children()
    {
        return $this->belongsToMany(Child::class, 'booking_child', 'booking_id', 'child_id')
            ->withTimestamps();
    }

    // Incluye soft-deleted para vistas históricas
    public function childrenWithTrashed()
    {
        return $this->belongsToMany(Child::class, 'booking_child', 'booking_id', 'child_id')
            ->withTimestamps()
            ->withTrashed();
    }

    // Relación con nanny a través de bookingAppointments (para mostrar en listados)
    public function nanny()
    {
        return $this->hasOneThrough(
            Nanny::class,
            BookingAppointment::class,
            'booking_id',  // Foreign key on booking_appointments table
            'id',           // Foreign key on nannies table
            'id',           // Local key on bookings table
            'nanny_id'      // Local key on booking_appointments table
        );
    }

    // public function getRouteKeyName()
    // {
    //     return 'ulid';
    // }
}
