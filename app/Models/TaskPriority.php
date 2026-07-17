<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskPriority extends Model
{
    protected $fillable = [
        'name',
        'level',
        'color',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'priority_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }
}
