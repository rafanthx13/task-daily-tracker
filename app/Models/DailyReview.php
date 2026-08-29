<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReview extends Model
{
    protected $fillable = [
        'date',
        'mood',
        'energy',
        'reviewed_at',
        'report_markdown',
        'report_snapshot',
    ];

    protected $casts = [
        'date' => 'date',
        'reviewed_at' => 'datetime',
        'report_snapshot' => 'array',
    ];
}
