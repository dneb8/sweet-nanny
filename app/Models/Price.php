<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'cost',
        'extra_hours',
        'night_shift',
        'special_care',
    ];

    // Relación uno a muchos con BookingAppointment
    public function bookingAppointments()
    {
        return $this->hasMany(BookingAppointment::class);
    }
}
