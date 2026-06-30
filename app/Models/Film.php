<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
protected $fillable = [
    'title', 'synopsis', 'director', 'country', 'year', 'duration', 'genre',
    'poster_path', 'trailer_url', 'participant_id', 'status'
];

    protected $casts = [
        'year' => 'integer',
        'duration' => 'integer',
    ];

    public function participant()
{
    return $this->belongsTo(User::class, 'participant_id');
}

public function applications()
{
    return $this->hasMany(Application::class);
}

public function juryScores()
{
    return $this->hasMany(JuryScore::class, 'film_id');
}
public function reviews()
{
    return $this->hasMany(Review::class);
}
}