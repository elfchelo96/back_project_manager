<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Servicio centralizado de auditoria general (tabla activity_logs).
 * Cualquier Service puede llamar a log() para dejar trazabilidad de
 * acciones relevantes (creacion/edicion/borrado de entidades, login, etc).
 */
class ActivityLogService
{
    public function log(?User $user, string $module, string $action, ?string $description = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip' => RequestFacade::ip(),
            'user_agent' => RequestFacade::header('User-Agent'),
        ]);
    }

    public function paginate(array $filters = [], int $perPage = 20)
    {
        $query = ActivityLog::query()->with('user')->latest('id');

        if (! empty($filters['module'])) {
            $query->module($filters['module']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }
}
