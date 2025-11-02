<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function nanny()
    {
        return $this->belongsTo(Nanny::class);
    }

    // public function price()
    // {
    //     return $this->belongsTo(Price::class);
    // }

    public function addresses()
    {
        return $this->belongsToMany(Address::class);
    }

    public function children()
    {
        return $this->belongsToMany(Child::class, 'booking_appointment_child', 'booking_appointment_id', 'child_id')
            ->withTimestamps();
    }

    public function childrenWithTrashed()
    {
        return $this->belongsToMany(Child::class, 'booking_appointment_child', 'booking_appointment_id', 'child_id')
            ->withTimestamps()
            ->withTrashed();
    }
}
