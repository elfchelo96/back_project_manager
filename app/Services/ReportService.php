<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Cantidad de tareas agrupadas por estado.
     */
    public function tasksByStatus(?int $projectId = null): array
    {
        $query = Task::query()
            ->join('task_statuses', 'task_statuses.id', '=', 'tasks.status_id')
            ->selectRaw('task_statuses.id as status_id, task_statuses.name as status, count(tasks.id) as total')
            ->groupBy('task_statuses.id', 'task_statuses.name');

        if ($projectId) {
            $query->where('tasks.project_id', $projectId);
        }

        return $query->get()->toArray();
    }

    /**
     * Cantidad de tareas agrupadas por usuario asignado.
     */
    public function tasksByUser(?int $projectId = null): array
    {
        $query = Task::query()
            ->join('users', 'users.id', '=', 'tasks.assigned_to')
            ->selectRaw("users.id as user_id, concat(users.firstname, ' ', users.lastname) as user, count(tasks.id) as total")
            ->groupBy('users.id', 'users.firstname', 'users.lastname');

        if ($projectId) {
            $query->where('tasks.project_id', $projectId);
        }

        return $query->get()->toArray();
    }

    /**
     * Horas trabajadas, agrupadas por usuario, dentro de un rango opcional de fechas.
     */
    public function hoursWorked(?string $from = null, ?string $to = null, ?int $projectId = null): array
    {
        $query = TimeEntry::query()
            ->join('users', 'users.id', '=', 'time_entries.user_id')
            ->join('tasks', 'tasks.id', '=', 'time_entries.task_id')
            ->selectRaw("users.id as user_id, concat(users.firstname, ' ', users.lastname) as user, sum(time_entries.hours) as total_hours")
            ->groupBy('users.id', 'users.firstname', 'users.lastname');

        if ($from) {
            $query->whereDate('time_entries.spent_on', '>=', $from);
        }

        if ($to) {
            $query->whereDate('time_entries.spent_on', '<=', $to);
        }

        if ($projectId) {
            $query->where('tasks.project_id', $projectId);
        }

        return $query->get()->toArray();
    }

    /**
     * Productividad por usuario: tareas completadas vs horas registradas.
     */
    public function productivity(?int $projectId = null): array
    {
        $query = User::query()
            ->select('users.id', 'users.firstname', 'users.lastname')
            ->withCount(['tasksAssigned as completed_tasks' => function ($q) use ($projectId) {
                $q->whereHas('status', fn ($s) => $s->where('is_closed', true));
                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            }])
            ->withSum('timeEntries as total_hours', 'hours');

        return $query->get()->map(function (User $user) {
            return [
                'user_id' => $user->id,
                'user' => trim("{$user->firstname} {$user->lastname}"),
                'completed_tasks' => (int) $user->completed_tasks,
                'total_hours' => (float) ($user->total_hours ?? 0),
            ];
        })->toArray();
    }

    public function activeProjects(): array
    {
        return Project::active()->withCount('tasks')->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'name' => $p->name,
                'tasks_count' => $p->tasks_count,
            ])->toArray();
    }

    public function finishedProjects(): array
    {
        return Project::closed()->withCount('tasks')->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'name' => $p->name,
                'tasks_count' => $p->tasks_count,
            ])->toArray();
    }
}
