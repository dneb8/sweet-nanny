<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        // 'nanny_id',
        'price_id',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'extra_hours',
        'total_cost',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // public function nanny()
    // {
    //     return $this->belongsTo(Nanny::class);
    // }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class);
    }
}
