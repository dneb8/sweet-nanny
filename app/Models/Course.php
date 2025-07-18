<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization',
        'date',
        'nanny_id',
    ];

    // Relación inversa con Nanny
    public function nanny()
    {
        return $this->belongsTo(Nanny::class);
    }
}
