<?php

namespace App\Enums;

enum NotificationType: string
{
    case Info = 'info';
    case TaskAssigned = 'task_assigned';
    case TaskStatusChanged = 'task_status_changed';
    case CommentAdded = 'comment_added';
    case ProjectMemberAdded = 'project_member_added';
    case DueDateReminder = 'due_date_reminder';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
