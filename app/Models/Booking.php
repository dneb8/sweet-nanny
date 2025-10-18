<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'description',
        'recurrent',
        'qualities',
        'degree',
        'courses',
    ];

    protected $casts = [
        'qualities' => 'array',
        'courses' => 'array',
        'recurrent' => 'boolean',
        'degree' => 'string', // Can be changed to DegreeEnum::class if needed
    ];

    // Relación uno a muchos con BookingAppointment
    public function bookingAppointments()
    {
        return $this->hasMany(BookingAppointment::class);
    }

    // Relación con Tutor (cada booking pertenece a un tutor)
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    // Relación con Address (morphOne - polymorphic)
    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }
    
    // Keep old belongsTo for backwards compatibility during migration
    public function addressOld()
    {
        return $this->belongsTo(Address::class);
    }

    // Relación con Child (cada booking puede tener varios niños)
    public function children()
    {
        return $this->belongsToMany(Child::class, 'booking_child', 'booking_id', 'child_id')
                    ->withTimestamps();
    }

    // Incluye soft-deleted para vistas históricas
    public function childrenWithTrashed()
    {
        return $this->belongsToMany(Child::class, 'booking_child', 'booking_id', 'child_id')
            ->withTimestamps()
            ->withTrashed();
    }

    // public function getRouteKeyName()
    // {
    //     return 'ulid';
    // }
}
