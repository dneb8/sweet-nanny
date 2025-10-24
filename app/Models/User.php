<?php

namespace App\Models;

use App\Eloquent\Builders\UserBuilder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
            ->singleFile();           // una sola foto de perfil (la nueva reemplaza la anterior)
    }

    /**
     * Get avatar URL (temporary signed URL for private S3 bucket)
     */
    public function avatarUrl(?int $minutes = 10): ?string
    {
        $media = $this->getFirstMedia('images');
        if (!$media) {
            return null;
        }

        // For private S3 buckets, use temporary URL
        // For public buckets, use getUrl() instead
        try {
            return $media->getTemporaryUrl(now()->addMinutes($minutes));
        } catch (\Exception $e) {
            // Fallback to regular URL if temporary URL generation fails
            return $media->getUrl();
        }
    }
}
