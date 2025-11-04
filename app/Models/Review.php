<?php

namespace App\Models;

use App\Eloquent\Builders\ReviewBuilder;
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
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    // Relación con Nanny (cuando reviewable es Nanny)
    public function nanny()
    {
        return $this->belongsTo(Nanny::class, 'reviewable_id')
            ->where('reviewable_type', Nanny::class);
    }

    // Relación con Tutor (cuando reviewable es Tutor)
    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'reviewable_id')
            ->where('reviewable_type', Tutor::class);
    }

    public function newEloquentBuilder($query): ReviewBuilder
    {
        return new ReviewBuilder($query);
    }
}

