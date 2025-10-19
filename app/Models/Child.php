<?php

namespace App\Models;

use App\Enums\Children\KinkshipEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Child extends Model
{
    use HasUlids, HasFactory, SoftDeletes;

    protected $casts = [
        'kinkship' => KinkshipEnum::class,
        'birthdate' => 'date',
    ];

    protected $fillable = [
        'tutor_id',
        'name',
        'birthdate',
        'kinkship',
    ];

    protected static function booted()
    {
        static::creating(function ($child) {
            $child->id = (string) Str::ulid();
        });
    }

    // Relación con Tutor 
    public function tutor()
    {
        return $this->belongsTo(Tutor::class); 
    }
}
