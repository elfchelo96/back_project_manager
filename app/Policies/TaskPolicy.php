<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') && $task->project->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        if (! $user->can('tasks.edit')) {
            return false;
        }

        return $task->author_id === $user->id
            || $task->assigned_to === $user->id
            || $task->project->owner_id === $user->id
            || $task->project->isMember($user);
    }

    public function delete(User $user, Task $task): bool
    {
        if (! $user->can('tasks.delete')) {
            return false;
        }

        return $task->author_id === $user->id || $task->project->owner_id === $user->id;
    }

    public function manageTime(User $user, Task $task): bool
    {
        return $user->can('time.manage') && $task->project->isMember($user);
    }
}
