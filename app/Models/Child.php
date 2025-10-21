<?php

namespace App\Models;

use App\Enums\Children\KinkshipEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasUlids, HasFactory, SoftDeletes;

    protected $casts = [
        'kinkship'  => KinkshipEnum::class,
        'birthdate' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'tutor_id',
        'name',
        'birthdate',
        'kinkship',
    ];

    // Genera ULID en la columna 'ulid' (no toca 'id')
    public function uniqueIds()
    {
        return ['ulid'];
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_child', 'child_id', 'booking_id')
                    ->withTimestamps();
    }

    public function getRouteKeyName()
    {
        return 'ulid';
    }
}
