<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title','notes','status','date','ordering'];

    protected $casts = [
        'date' => 'date',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
