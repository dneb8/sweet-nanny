<?php

namespace App\Models;

use App\Enums\Career\DegreeEnum;
use App\Enums\Career\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relación N:N con careers
    public function nannies()
    {
        return $this->belongsToMany(Nanny::class, 'career_nanny')
            ->withPivot('degree', 'status', 'institution')
            ->withCasts(['status' => StatusEnum::class, 'degree' => DegreeEnum::class])
            ->withTimestamps();
    }
}
