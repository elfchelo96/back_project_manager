<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionService
{
    public function all(): Collection
    {
        return Permission::withCount('roles')->orderBy('name')->get();
    }

    public function find(int $id): Permission
    {
        return Permission::withCount('roles')->findOrFail($id);
    }

    public function create(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            return Permission::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ])->loadCount('roles');
        });
    }

    public function update(Permission $permission, array $data): Permission
    {
        if (! empty($data['name'])) {
            $permission->update(['name' => $data['name']]);
        }

        return $permission->refresh()->loadCount('roles');
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }

    public function forUser(User $user): Collection
    {
        return $user->getAllPermissions();
    }

    /**
     * Agrupa los permisos por su modulo (prefijo antes del punto),
     * util para construir checkboxes agrupados en el frontend.
     */
    public function grouped(): \Illuminate\Support\Collection
    {
        return $this->all()->groupBy(function (Permission $permission) {
            return explode('.', $permission->name)[0];
        });
    }
}
