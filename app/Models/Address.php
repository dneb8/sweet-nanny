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

    public function tutors()
    {
        return $this->hasOne(Tutor::class);
    }

    public function nannies()
    {
        return $this->hasOne(Tutor::class);
    }

    public function bookingServices()
    {
        return $this->belongsToMany(BookingService::class);
    }
}
