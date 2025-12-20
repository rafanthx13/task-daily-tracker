<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'notes', 'status', 'date', 'ordering', 'id_original', 'repeat_days_left'];

    protected $casts = [
        'date' => 'date',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
