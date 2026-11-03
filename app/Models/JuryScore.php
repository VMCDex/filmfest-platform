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

    // Итоговая оценка — среднее по пяти критериям
    protected static function booted()
    {
        static::saving(function (JuryScore $score) {
            $score->total_score = round((
                $score->criterion_script
                + $score->criterion_director
                + $score->criterion_acting
                + $score->criterion_cinematography
                + $score->criterion_sound
            ) / 5, 2);
        });
    }
}