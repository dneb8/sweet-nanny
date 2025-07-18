<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'tutor_id',
        // 'address_id',
        'description',
        'recurrent',
    ];

    // Relación uno a muchos con BookingService
    public function bookingServices()
    {
       return $this->hasMany(BookingService::class);
    }
}
