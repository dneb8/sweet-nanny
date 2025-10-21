<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'address_id',
        'description',
        'recurrent',
        'qualities',
        'career',
        'courses',
    ];

    protected $casts = [
        'qualities' => 'array',
        'courses' => 'array',
        'recurrent' => 'boolean',
        'career' => 'array',
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

    // Relación con Address (belongsTo - foreign key reference)
    // Address remains owned by Tutor, booking just references it
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    
    // Deprecated: Polymorphic relation (kept for migration)
    public function addressPolymorphic()
    {
        return $this->morphOne(Address::class, 'addressable');
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
