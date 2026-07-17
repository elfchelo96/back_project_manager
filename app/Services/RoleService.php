<?php

namespace App\Services;

use App\Exceptions\ProtectedRoleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleService
{
    private const SYSTEM_ROLES = ['Super Administrador', 'Administrador'];

    public function all(): Collection
    {
        return Role::withCount('permissions')->orderBy('name')->get();
    }

    public function find(int $id): Role
    {
        return Role::with('permissions')->withCount('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            if (! empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions')->loadCount('permissions');
        });
    }

    public function update(Role $role, array $data): Role
    {
        if ($this->isSystemRole($role->name)) {
            throw new ProtectedRoleException("No se puede modificar el rol del sistema: {$role->name}");
        }

        if (! empty($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        return $role->refresh()->load('permissions')->loadCount('permissions');
    }

    public function delete(Role $role): void
    {
        if ($this->isSystemRole($role->name)) {
            throw new ProtectedRoleException("No se puede eliminar el rol del sistema: {$role->name}");
        }

        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->refresh()->load('permissions')->loadCount('permissions');
    }

    public function addPermissions(Role $role, array $permissions): Role
    {
        $existingPermissions = $role->permissions->pluck('name')->toArray();
        $allPermissions = array_unique(array_merge($existingPermissions, $permissions));

        $role->syncPermissions($allPermissions);

        return $role->refresh()->load('permissions')->loadCount('permissions');
    }

    public function removePermissions(Role $role, array $permissions): Role
    {
        $existingPermissions = $role->permissions->pluck('name')->toArray();
        $remainingPermissions = array_diff($existingPermissions, $permissions);

        $role->syncPermissions($remainingPermissions);

        return $role->refresh()->load('permissions')->loadCount('permissions');
    }

    public function assignToUser(Role $role, User $user): void
    {
        $user->assignRole($role->name);
    }

    public function revokeFromUser(Role $role, User $user): void
    {
        $user->removeRole($role->name);
    }

    public function usersWithRole(Role $role)
    {
        return $role->users()->get();
    }

    private function isSystemRole(string $roleName): bool
    {
        return in_array($roleName, self::SYSTEM_ROLES, true);
    }
}
