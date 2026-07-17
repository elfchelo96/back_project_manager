<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'category_id',
        'status_id',
        'priority_id',
        'author_id',
        'assigned_to',
        'parent_id',
        'subject',
        'description',
        'estimated_hours',
        'spent_hours',
        'done_ratio',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'estimated_hours' => 'decimal:2',
            'spent_hours' => 'decimal:2',
            'done_ratio' => 'integer',
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /** Tareas de las cuales ESTA tarea depende (deben resolverse antes). */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withPivot('type')->withTimestamps();
    }

    /** Tareas que dependen de ESTA tarea. */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withPivot('type')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
            ->whereHas('status', fn ($q) => $q->where('is_closed', false));
    }

    public function scopeRootTasks($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('subject', 'ilike', "%{$term}%")
                ->orWhere('description', 'ilike', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    protected function isOverdue(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->due_date
                && $this->due_date->isPast()
                && ! $this->isClosed(),
        );
    }

    public function isClosed(): bool
    {
        return (bool) ($this->status?->is_closed ?? false);
    }

    public function canBeClosed(): bool
    {
        // No puede cerrarse si tiene dependencias abiertas tipo "blocks"
        return ! $this->dependencies()
            ->wherePivot('type', 'blocks')
            ->whereHas('status', fn ($q) => $q->where('is_closed', false))
            ->exists();
    }
}
