<?php

namespace App\Models;

use App\Eloquent\Builders\UserBuilder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, HasRoles, HasUlids, InteractsWithMedia, Notifiable;

    protected $fillable = ['name', 'surnames', 'email', 'number', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = [
        'avatar_url',
        'avatar_status',
        'avatar_note',
    ];

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
        return ['ulid'];
    }

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

    // 🔹 Spatie: define colección y disco
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('s3')           // usa el disco S3
            ->singleFile();           // una sola foto de perfil
    }

    /**
     * Helper: URL firmada si el bucket es privado; si falla, pública.
     * (renombrado para no colisionar con el accessor moderno)
     */
    public function avatarSignedOrPublicUrl(?int $minutes = 10): ?string
    {
        $media = $this->getFirstMedia('images');
        if (! $media) {
            return null;
        }

        try {
            return $media->getTemporaryUrl(now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            return $media->getUrl();
        }
    }

    // =========================
    // Accessors ($appends)
    // =========================

    protected function avatarUrl(): Attribute
    {
        // expone 'avatar_url'
        return Attribute::get(fn () => $this->avatarSignedOrPublicUrl());
    }

    protected function avatarStatus(): Attribute
    {
        // expone 'avatar_status'
        return Attribute::get(function () {
            $m = $this->getFirstMedia('images');

            return $m?->getCustomProperty('status', 'approved');
        });
    }

    protected function avatarNote(): Attribute
    {
        // expone 'avatar_note'
        return Attribute::get(function () {
            $m = $this->getFirstMedia('images');

            return $m?->getCustomProperty('note');
        });
    }
}
