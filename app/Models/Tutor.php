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

    public function uniqueIds()
    {
        return [
            'ulid',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

}
