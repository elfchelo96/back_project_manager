<?php

namespace Database\Seeders;

use App\Models\TaskPriority;
use Illuminate\Database\Seeder;

class TaskPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            ['name' => 'Baja', 'level' => 1, 'color' => '#6b7280'],
            ['name' => 'Normal', 'level' => 2, 'color' => '#3b82f6'],
            ['name' => 'Alta', 'level' => 3, 'color' => '#f59e0b'],
            ['name' => 'Urgente', 'level' => 4, 'color' => '#f97316'],
            ['name' => 'Critica', 'level' => 5, 'color' => '#ef4444'],
        ];

        foreach ($priorities as $priority) {
            TaskPriority::firstOrCreate(['name' => $priority['name']], $priority);
        }
    }
}
