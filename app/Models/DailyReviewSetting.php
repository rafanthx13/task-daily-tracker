<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReviewSetting extends Model
{
    protected $fillable = ['show_extra_lane', 'show_reminders', 'show_mood_energy'];

    protected $casts = [
        'show_extra_lane' => 'boolean',
        'show_reminders' => 'boolean',
        'show_mood_energy' => 'boolean',
    ];
}
