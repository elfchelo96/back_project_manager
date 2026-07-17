<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Repositories\Contracts\TimeEntryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TimeEntryService
{
    public function __construct(protected TimeEntryRepositoryInterface $timeEntries)
    {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->timeEntries->paginate($perPage, ['task', 'user'], function ($query) use ($filters) {
            if (! empty($filters['task_id'])) {
                $query->where('task_id', $filters['task_id']);
            }

            if (! empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (! empty($filters['from'])) {
                $query->whereDate('spent_on', '>=', $filters['from']);
            }

            if (! empty($filters['to'])) {
                $query->whereDate('spent_on', '<=', $filters['to']);
            }
        });
    }

    public function create(Task $task, User $user, array $data): TimeEntry
    {
        $entry = $this->timeEntries->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'hours' => $data['hours'],
            'comments' => $data['comments'] ?? null,
            'spent_on' => $data['spent_on'] ?? now()->toDateString(),
        ]);

        $this->recalculateSpentHours($task);

        return $entry->load(['task', 'user']);
    }

    public function update(TimeEntry $entry, array $data): TimeEntry
    {
        $this->timeEntries->update($entry, array_filter([
            'hours' => $data['hours'] ?? null,
            'comments' => $data['comments'] ?? null,
            'spent_on' => $data['spent_on'] ?? null,
        ], fn ($v) => $v !== null));

        $this->recalculateSpentHours($entry->task);

        return $entry->refresh();
    }

    public function delete(TimeEntry $entry): void
    {
        $task = $entry->task;
        $this->timeEntries->delete($entry);
        $this->recalculateSpentHours($task);
    }

    protected function recalculateSpentHours(Task $task): void
    {
        $task->forceFill([
            'spent_hours' => $this->timeEntries->totalHoursForTask($task->id),
        ])->save();
    }
}
