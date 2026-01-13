<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeManagementTag extends Model
{
    protected $fillable = ['name', 'color'];

    public function entries(): HasMany
    {
        return $this->hasMany(TimeManagementEntry::class, 'tag_id');
    }
}
