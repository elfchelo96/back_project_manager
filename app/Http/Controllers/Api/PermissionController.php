<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use App\Traits\ApiResponser;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponser;

    public function __construct(protected PermissionService $permissionService)
    {
    }

    public function index()
    {
        return $this->success(PermissionResource::collection($this->permissionService->all()));
    }

    public function grouped()
    {
        return $this->success($this->permissionService->grouped());
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = $this->permissionService->create($request->validated());

        return $this->created(new PermissionResource($permission), 'Permiso creado correctamente.');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission = $this->permissionService->update($permission, $request->validated());

        return $this->success(new PermissionResource($permission), 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('permissions.manage');

        $this->permissionService->delete($permission);

        return $this->noContentMessage('Permiso eliminado correctamente.');
    }
}
