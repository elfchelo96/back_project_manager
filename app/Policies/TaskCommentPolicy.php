<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function create(User $user, TaskComment $comment): bool
    {
        return $comment->task->project->isMember($user);
    }

    public function update(User $user, TaskComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $comment->user_id === $user->id || $comment->task->project->owner_id === $user->id;
    }
}
