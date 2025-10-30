<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerNanny extends Model
{
    use HasFactory;

    protected $table = 'career_nanny'; // Importante para coincidir con el nombre real

    protected $fillable = [
        'career_id',
        'nanny_id',
        'education_level',
        'occupation',
    ];
}
