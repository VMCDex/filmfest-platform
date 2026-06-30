<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'user_id', 'film_id', 'ip_hash'
    ];

    public $timestamps = false;
}