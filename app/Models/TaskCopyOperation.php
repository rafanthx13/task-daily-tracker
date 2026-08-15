<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCopyOperation extends Model
{
    protected $fillable = ['source_date', 'destination_date'];

    protected $casts = [
        'source_date' => 'date',
        'destination_date' => 'date',
    ];
}
