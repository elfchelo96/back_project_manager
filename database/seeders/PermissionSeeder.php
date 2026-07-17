<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Permisos del sistema, expandidos a partir de los modulos indicados
     * en la especificacion (projects.*, tasks.*, users.*, etc).
     */
    public static array $permissions = [
        'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
        'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.manage',
        'permissions.manage',
        'reports.view',
        'wiki.manage',
        'time.manage',
    ];

    public function run(): void
    {
        foreach (self::$permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
