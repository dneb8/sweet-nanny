<?php

namespace App\Models;

use App\Enums\Address\TypeEnum;
use App\Enums\Address\ZoneEnum;
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
        'latitude',
        'longitude',
        'type',
        'zone',
        'other_type',
        'internal_number',
        'addressable_type',
        'addressable_id',
    ];

    public function uniqueIds()
    {
        return ['ulid'];
    }

    protected $casts = [
        'type' => TypeEnum::class, // Enum backeado por string
        'zone' => ZoneEnum::class,
    ];

    /**
     * Relación polimórfica.
     */
    public function addressable()
    {
        return $this->morphTo();
    }

}
