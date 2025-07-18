<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewable_type',  
        'reviewable_id',   
        'rating',
        'comments',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }
}
