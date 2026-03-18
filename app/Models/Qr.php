<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qr extends Model
{
    protected $fillable = ['user_id', 'content', 'settings'];

    protected $casts = [
        'settings' => 'array'
    ];
}