<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeManagementEntry extends Model
{
    protected $fillable = [
        'date',
        'task_name',
        'start_time',
        'end_time',
        'tag_id'
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(TimeManagementTag::class, 'tag_id');
    }

    /**
     * Get the difference between start_time and end_time.
     */
    public function getTimeDifferenceAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        $diff = $start->diff($end);
        return sprintf('%02d:%02d', ($diff->h + ($diff->days * 24)), $diff->i);
    }
}
