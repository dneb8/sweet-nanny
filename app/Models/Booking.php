<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'address_id',
        'description',
        'recurrent',
    ];

    // Relación uno a muchos con BookingAppointment
    public function bookingServices()
    {
        return $this->hasMany(BookingAppointment::class);
    }

    // Relación con Tutor (cada booking pertenece a un tutor)
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    // Relación con Address (cada booking pertenece a una dirección)
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
