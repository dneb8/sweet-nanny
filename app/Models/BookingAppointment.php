<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\Booking\StatusEnum; 
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
