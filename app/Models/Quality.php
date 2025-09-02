<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relación N:N con nannies
    public function nannies()
    {
        return $this->belongsToMany(Nanny::class, 'nanny_qualities')->withTimestamps();
    }
}
