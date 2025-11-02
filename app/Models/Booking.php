<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'description',
        'recurrent',
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



    // public function getRouteKeyName()
    // {
    //     return 'ulid';
    // }
}
