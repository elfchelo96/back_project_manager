<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

class TaskCommentService
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    public function paginate(Task $task, int $perPage = 20)
    {
        return $task->comments()->with('user')->paginate($perPage);
    }

    public function create(Task $task, User $user, string $comment): TaskComment
    {
        $created = $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
        ]);

        $notifyUserId = $task->author_id !== $user->id ? $task->author_id : $task->assigned_to;

        if ($notifyUserId && $notifyUserId !== $user->id) {
            $this->notifications->notify(
                $notifyUserId,
                'Nuevo comentario',
                "Nuevo comentario en la tarea \"{$task->subject}\".",
                'comment_added'
            );
        }

        return $created->load('user');
    }

    public function update(TaskComment $comment, string $text): TaskComment
    {
        $comment->update(['comment' => $text]);

        return $comment->refresh();
    }

    public function delete(TaskComment $comment): void
    {
        $comment->delete();
    }
}
