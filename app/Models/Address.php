<?php

namespace App\Models;

use App\Enums\Address\TypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'addresses';

    protected $fillable = [
        'postal_code',
        'street',
        'neighborhood',
        'type',
        'other_type',
        'internal_number',
        'addressable_type',
        'addressable_id',
    ];

    public function uniqueIds()
    {
        return [
            'ulid',
        ];
    }

    protected $casts = [
        'type' => TypeEnum::class
    ];

    /**
     * Get the ownable model (Tutor, Nanny, or Booking).
     */
    public function addressable()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bookingAppointments()
    {
        return $this->belongsToMany(BookingAppointment::class);
    }
}
