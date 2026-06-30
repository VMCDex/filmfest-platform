<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JuryScore extends Model
{
    protected $fillable = [
        'jury_id', 'film_id', 'criterion_script', 'criterion_director', 
        'criterion_acting', 'criterion_cinematography', 'criterion_sound'
    ];

    protected $casts = [
        'total_score' => 'decimal:2'
    ];

    public $timestamps = false;
}