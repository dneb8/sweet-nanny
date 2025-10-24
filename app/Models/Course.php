<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Course\NameEnum;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'organization',
        'date',
        'nanny_id',
    ];

     protected $casts = [
        'name' => NameEnum::class,
    ];

    // Relación inversa con Nanny
    public function nanny()
    {
        return $this->belongsTo(Nanny::class);
    }
}
