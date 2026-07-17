<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Nuevo', 'color' => '#6b7280', 'is_closed' => false, 'position' => 1],
            ['name' => 'En Progreso', 'color' => '#3b82f6', 'is_closed' => false, 'position' => 2],
            ['name' => 'Pendiente', 'color' => '#f59e0b', 'is_closed' => false, 'position' => 3],
            ['name' => 'En Pruebas', 'color' => '#8b5cf6', 'is_closed' => false, 'position' => 4],
            ['name' => 'Resuelto', 'color' => '#10b981', 'is_closed' => false, 'position' => 5],
            ['name' => 'Cerrado', 'color' => '#22c55e', 'is_closed' => true, 'position' => 6],
            ['name' => 'Cancelado', 'color' => '#ef4444', 'is_closed' => true, 'position' => 7],
        ];

        foreach ($statuses as $status) {
            TaskStatus::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
