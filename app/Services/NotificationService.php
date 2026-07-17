<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function notify(int $userId, string $title, string $message, string $type = 'info'): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }

    public function paginate(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $user->appNotifications()->latest('id');

        if (array_key_exists('unread', $filters) && $filters['unread']) {
            $query->unread();
        }

        return $query->paginate($perPage);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();

        return $notification->refresh();
    }

    public function markAllAsRead(User $user): int
    {
        return $user->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function delete(Notification $notification): void
    {
        $notification->delete();
    }

    public function unreadCount(User $user): int
    {
        return $user->appNotifications()->unread()->count();
    }
}
