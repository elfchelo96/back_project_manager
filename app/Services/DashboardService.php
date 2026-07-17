<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;

class DashboardService
{
    public function summary(): array
    {
        return [
            'projects' => Project::count(),
            'tasks' => Task::count(),
            'completed_tasks' => Task::whereHas('status', fn ($q) => $q->where('is_closed', true))->count(),
            'pending_tasks' => Task::whereHas('status', fn ($q) => $q->where('is_closed', false))->count(),
            'users' => User::count(),
            'hours_logged' => (float) TimeEntry::sum('hours'),
        ];
    }
}
