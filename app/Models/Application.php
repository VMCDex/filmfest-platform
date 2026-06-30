<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'film_id', 'event_id', 'status', 'comment', 'reviewed_by'
    ];

    // === ДОБАВЬТЕ ЭТИ СТРОКИ ===
    public function film()
    {
        return $this->belongsTo(Film::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    // ==========================
}