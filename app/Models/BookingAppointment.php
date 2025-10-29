<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\Booking\StatusEnum; 

class BookingAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'nanny_id',
        // 'price_id',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'extra_hours',
        'total_cost',
    ];

    // Aquí el cast a enum
    protected $casts = [
        'status' => StatusEnum::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function nanny()
    {
        return $this->belongsTo(Nanny::class);
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class);
    }
}
