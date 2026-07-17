<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponser;

    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function index(Request $request)
    {
        $notifications = $this->notificationService->paginate($request->user(), $request->only(['unread']));

        return $this->paginated($notifications, NotificationResource::class);
    }

    public function unreadCount(Request $request)
    {
        return $this->success(['count' => $this->notificationService->unreadCount($request->user())]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('No tiene permisos para modificar esta notificacion.', 403);
        }

        $notification = $this->notificationService->markAsRead($notification);

        return $this->success(new NotificationResource($notification), 'Notificacion marcada como leida.');
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->noContentMessage('Todas las notificaciones fueron marcadas como leidas.');
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('No tiene permisos para eliminar esta notificacion.', 403);
        }

        $this->notificationService->delete($notification);

        return $this->noContentMessage('Notificacion eliminada correctamente.');
    }
}
