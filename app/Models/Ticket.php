<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'seat_number',
        'price',
        'status',
        'paid_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    // Если нужна связь с пользователем (покупателем):
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}