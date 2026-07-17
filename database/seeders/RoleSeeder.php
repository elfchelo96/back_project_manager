<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Mapeo Rol => permisos asignados por defecto.
     * Super Administrador omite ademas todas las Policies via Gate::before,
     * pero igual recibe todos los permisos explicitamente para que sea
     * visible/consistente en la interfaz de administracion.
     */
    public static array $roleDefaults = [
        'Super Administrador' => ['*'],
        'Administrador' => [
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'reports.view', 'wiki.manage', 'time.manage',
        ],
        'Project Manager' => [
            'projects.view', 'projects.edit',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'users.view', 'reports.view', 'wiki.manage', 'time.manage',
        ],
        'Desarrollador' => [
            'projects.view',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'wiki.manage', 'time.manage',
        ],
        'QA' => [
            'projects.view',
            'tasks.view', 'tasks.edit',
            'time.manage',
        ],
        'Cliente' => [
            'projects.view', 'tasks.view', 'reports.view',
        ],
        'Invitado' => [
            'projects.view', 'tasks.view',
        ],
    ];

    public function run(): void
    {
        foreach (array_keys(self::$roleDefaults) as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        foreach (self::$roleDefaults as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if ($permissions === ['*']) {
                $role->syncPermissions(PermissionSeeder::$permissions);
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
