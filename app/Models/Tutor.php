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
    
    // Polymorphic relation to addresses
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
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

    /**
     * Get avatar URL from related User
     */
    public function avatarUrl(?int $minutes = 10): ?string
    {
        return $this->user?->avatarSignedOrPublicUrl($minutes);
    }
}
