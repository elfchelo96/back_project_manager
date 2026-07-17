<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(
        protected TaskRepositoryInterface $tasks,
        protected ActivityLogService $activityLog,
        protected NotificationService $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tasks->filtered($filters)
            ->with(['status', 'priority', 'category', 'author', 'assignee', 'project'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(string $uuid): Task
    {
        return Task::with([
            'status', 'priority', 'category', 'author', 'assignee', 'project',
            'parent', 'children', 'dependencies', 'dependents',
        ])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data, User $author): Task
    {
        return DB::transaction(function () use ($data, $author) {
            $statusId = $data['status_id'] ?? TaskStatus::orderBy('position')->value('id');
            $priorityId = $data['priority_id'] ?? TaskPriority::orderBy('level')->value('id');

            $task = $this->tasks->create([
                'project_id' => $data['project_id'],
                'category_id' => $data['category_id'] ?? null,
                'status_id' => $statusId,
                'priority_id' => $priorityId,
                'author_id' => $author->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? null,
                'spent_hours' => 0,
                'done_ratio' => $data['done_ratio'] ?? 0,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            if (! empty($data['dependencies'])) {
                foreach ($data['dependencies'] as $dependency) {
                    $task->dependencies()->attach($dependency['task_id'], ['type' => $dependency['type'] ?? 'blocks']);
                }
            }

            $this->activityLog->log($author, 'tasks', 'create', "Tarea creada: {$task->subject}");

            if ($task->assigned_to) {
                $this->notifications->notify(
                    $task->assigned_to,
                    'Nueva tarea asignada',
                    "Se le ha asignado la tarea \"{$task->subject}\".",
                    'task_assigned'
                );
            }

            return $task->load(['status', 'priority', 'category', 'author', 'assignee']);
        });
    }

    public function update(Task $task, array $data, User $actor): Task
    {
        return DB::transaction(function () use ($task, $data, $actor) {
            $original = $task->only(['status_id', 'priority_id', 'assigned_to']);

            $payload = array_filter([
                'category_id' => $data['category_id'] ?? null,
                'status_id' => $data['status_id'] ?? null,
                'priority_id' => $data['priority_id'] ?? null,
                'assigned_to' => array_key_exists('assigned_to', $data) ? $data['assigned_to'] : null,
                'parent_id' => array_key_exists('parent_id', $data) ? $data['parent_id'] : null,
                'subject' => $data['subject'] ?? null,
                'description' => $data['description'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? null,
                'done_ratio' => array_key_exists('done_ratio', $data) ? $data['done_ratio'] : null,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ], fn ($v) => $v !== null);

            // Validar que la tarea pueda cerrarse si el nuevo estado es "cerrado"
            if (! empty($payload['status_id'])) {
                $newStatus = TaskStatus::find($payload['status_id']);
                if ($newStatus && $newStatus->is_closed && ! $task->canBeClosed()) {
                    throw ValidationException::withMessages([
                        'status_id' => ['No se puede cerrar la tarea: tiene dependencias bloqueantes sin resolver.'],
                    ]);
                }
            }

            $this->tasks->update($task, $payload);
            $task->refresh();

            $this->recordHistory($task, $original, $actor);

            if (array_key_exists('assigned_to', $payload) && $payload['assigned_to'] !== $original['assigned_to'] && $task->assigned_to) {
                $this->notifications->notify(
                    $task->assigned_to,
                    'Tarea asignada',
                    "Se le ha asignado la tarea \"{$task->subject}\".",
                    'task_assigned'
                );
            }

            $this->activityLog->log($actor, 'tasks', 'update', "Tarea actualizada: {$task->subject}");

            return $task->load(['status', 'priority', 'category', 'author', 'assignee']);
        });
    }

    protected function recordHistory(Task $task, array $original, User $actor): void
    {
        $fieldsToTrack = [
            'status_id' => fn ($id) => $id ? TaskStatus::find($id)?->name : null,
            'priority_id' => fn ($id) => $id ? TaskPriority::find($id)?->name : null,
            'assigned_to' => fn ($id) => $id ? User::find($id)?->fullName : 'Sin asignar',
        ];

        foreach ($fieldsToTrack as $field => $resolver) {
            $newValue = $task->{$field};

            if ($original[$field] === $newValue) {
                continue;
            }

            $task->history()->create([
                'user_id' => $actor->id,
                'field_changed' => $field,
                'old_value' => $resolver($original[$field]),
                'new_value' => $resolver($newValue),
            ]);
        }
    }

    public function delete(Task $task, ?User $actor = null): void
    {
        $this->tasks->delete($task);
        $this->activityLog->log($actor, 'tasks', 'delete', "Tarea eliminada: {$task->subject}");
    }

    public function addDependency(Task $task, int $dependsOnTaskId, string $type = 'blocks'): void
    {
        if ($dependsOnTaskId === $task->id) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['Una tarea no puede depender de si misma.'],
            ]);
        }

        $task->dependencies()->syncWithoutDetaching([$dependsOnTaskId => ['type' => $type]]);
    }

    public function removeDependency(Task $task, int $dependsOnTaskId): void
    {
        $task->dependencies()->detach($dependsOnTaskId);
    }

    public function history(Task $task)
    {
        return $task->history()->with('user')->latest('id')->paginate(20);
    }
}
