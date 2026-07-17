<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AddPermissionsRequest;
use App\Http\Requests\Role\AssignPermissionsRequest;
use App\Http\Requests\Role\AssignUserRoleRequest;
use App\Http\Requests\Role\RemovePermissionsRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use App\Traits\ApiResponser;

class RoleController extends Controller
{
    use ApiResponser;

    public function __construct(protected RoleService $roleService)
    {
    }

    public function index()
    {
        return $this->success(RoleResource::collection($this->roleService->all()));
    }

    public function show(Role $role)
    {
        return $this->success(new RoleResource($this->roleService->find($role->id)));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());

        return $this->created(new RoleResource($role), 'Rol creado correctamente.');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role = $this->roleService->update($role, $request->validated());

        return $this->success(new RoleResource($role), 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('roles.manage');

        $this->roleService->delete($role);

        return $this->noContentMessage('Rol eliminado correctamente.');
    }

    public function syncPermissions(AssignPermissionsRequest $request, Role $role)
    {
        $role = $this->roleService->syncPermissions($role, $request->validated('permissions'));

        return $this->success(new RoleResource($role), 'Permisos sincronizados correctamente.');
    }

    public function addPermissions(AddPermissionsRequest $request, Role $role)
    {
        $role = $this->roleService->addPermissions($role, $request->validated('permissions'));

        return $this->success(new RoleResource($role), 'Permisos agregados correctamente.');
    }

    public function removePermissions(RemovePermissionsRequest $request, Role $role)
    {
        $role = $this->roleService->removePermissions($role, $request->validated('permissions'));

        return $this->success(new RoleResource($role), 'Permisos removidos correctamente.');
    }

    public function users(Role $role)
    {
        return $this->success(UserResource::collection($this->roleService->usersWithRole($role)));
    }

    public function assignUser(AssignUserRoleRequest $request, Role $role)
    {
        $user = User::findOrFail($request->validated('user_id'));

        $this->roleService->assignToUser($role, $user);

        return $this->success(new RoleResource($role->load('permissions')->loadCount(['users', 'permissions'])), 'Rol asignado al usuario correctamente.');
    }

    public function revokeUser(Role $role, User $user)
    {
        $this->authorize('roles.manage');

        $this->roleService->revokeFromUser($role, $user);

        return $this->noContentMessage('Rol revocado del usuario correctamente.');
    }
}
