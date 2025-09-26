<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nanny extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = [
        'user_id',
        'bio',
        'availability',
        'start_date',
        'address_id'
    ];

    // Relación 1:N con Course
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // Relación N:N con careers
    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_nanny')
        ->withPivot('degree', 'status', 'institution') // ✅ estos sí existen
        ->withTimestamps();
    }


    // Relación N:N con cualidades
    public function qualities()
    {
         return $this->belongsToMany(Quality::class, 'nanny_qualities')->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Relación 1:N con servicios 
    public function bookingServices()
    {
        return $this->hasMany(BookingAppointment::class);
    }
    public function reviews()
    {
    return $this->morphMany(Review::class, 'reviewable');}
    
    public function uniqueIds()
    {
        // Generación automática de ulid para la columna ulid.
        return [
            'ulid',
        ];
    }

    /**
     * Usar ulid para obtener los modelos en los parámetros de rutas.
     */
    public function getRouteKeyName()
    {
        return 'ulid';
    }
}
