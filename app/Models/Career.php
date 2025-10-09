<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'area'];

    // Relación N:N con careers
    public function nannies()
    {
        return $this->belongsToMany(Nanny::class, 'career_nanny')
                    ->withPivot('degree', 'status', 'institution')
                    ->withTimestamps();
    }

}


