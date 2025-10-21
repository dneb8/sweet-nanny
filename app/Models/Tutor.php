<?php

namespace App\Models;

use App\Enums\Tutor\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tutor extends Model
{
    /** @use HasFactory<\Database\Factories\TutorFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'tutors';

    protected $fillable = [
        'emergency_contact',
        'emergency_number',
    ];

    protected $casts = [
        'verification_status' => VerificationStatusEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function children()
    {
        return $this->hasMany(Child::class);
    }

    /**
     * Get addresses through user relationship
     * Returns a collection to match expected interface
     */
    public function addresses()
    {
        // Return user's address as a collection for consistency
        // This is an accessor that wraps the single address in a collection
        return $this->hasOneThrough(
            Address::class,
            User::class,
            'id',           // Foreign key on users table
            'id',           // Foreign key on addresses table
            'user_id',      // Local key on tutors table
            'address_id'    // Local key on users table
        );
    }

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
