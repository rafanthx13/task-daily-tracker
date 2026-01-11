<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['title', 'type', 'last_completed_at'];

    protected $casts = [
        'last_completed_at' => 'datetime',
    ];

    public function getDurationAttribute()
    {
        if (!$this->last_completed_at) return null;
        return $this->created_at->diffForHumans($this->last_completed_at, true);
    }
}
