<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view') && $project->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        if (! $user->can('projects.edit')) {
            return false;
        }

        if ($project->owner_id === $user->id) {
            return true;
        }

        return $project->projectMembers()
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->where('name', 'Project Manager'))
            ->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete') && $project->owner_id === $user->id;
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
