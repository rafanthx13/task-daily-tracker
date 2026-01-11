<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['title', 'type', 'last_completed_at'];

    protected $casts = [
        'last_completed_at' => 'datetime',
    ];
}
