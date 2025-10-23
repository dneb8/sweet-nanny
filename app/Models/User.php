<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Eloquent\Builders\UserBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surnames',
        'email',
        'number',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    public function nanny()
    {
        return $this->hasOne(Nanny::class);
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
     * Registra un custom Builder.
     */
    public function newEloquentBuilder($query): Builder
    {
        return new UserBuilder($query);
    }
}
