<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasFactory, Notifiable, HasRoles, HasUlids, InteractsWithMedia;

    protected $fillable = ['name','surnames','email','number','password'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tutor() { return $this->hasOne(Tutor::class); }
    public function nanny() { return $this->hasOne(Nanny::class); }

    public function uniqueIds() { return ['ulid']; }
    public function getRouteKeyName() { return 'ulid'; }

    // 🔹 Spatie: define colección y disco
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('s3')           // usa el disco S3
            ->singleFile();           // una sola foto de perfil (la nueva reemplaza la anterior)
    }

}
