<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Career\StatusEnum;
use App\Enums\Career\DegreeEnum;

class Career extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relación N:N con careers
    public function nannies()
    {
        return $this->belongsToMany(Nanny::class, 'career_nanny')
                    ->withPivot('degree', 'status', 'institution')
                    ->withCasts(['status' => StatusEnum::class,])
                    ->withCasts(['degree' => DegreeEnum::class,])
                    ->withTimestamps();
    }

}


