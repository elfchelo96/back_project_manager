<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            return;
        }

        $developers = User::whereIn('email', ['diego.dev@empresa.com', 'daniela.dev@empresa.com'])->get();
        $qa = User::where('email', 'quentin.qa@empresa.com')->first();
        $pm = User::where('email', 'pedro.pm@empresa.com')->first();

        $newStatusId = TaskStatus::where('name', 'Nuevo')->value('id');
        $progressStatusId = TaskStatus::where('name', 'En Progreso')->value('id');
        $closedStatusId = TaskStatus::where('name', 'Cerrado')->value('id');
        $normalPriorityId = TaskPriority::where('name', 'Normal')->value('id');
        $highPriorityId = TaskPriority::where('name', 'Alta')->value('id');

        $subjects = [
            'Diseñar el modelo de datos',
            'Implementar autenticacion de usuarios',
            'Crear endpoints de la API REST',
            'Configurar pipeline de CI/CD',
            'Escribir pruebas automatizadas',
            'Revisar y optimizar consultas SQL',
        ];

        foreach ($projects as $project) {
            // Pagina wiki inicial del proyecto.
            $project->wikiPages()->firstOrCreate(
                ['title' => 'Inicio'],
                [
                    'content' => "# {$project->name}\n\nDocumentacion general del proyecto. Agregue aqui notas relevantes para el equipo.",
                    'created_by' => $pm->id,
                    'updated_by' => $pm->id,
                ]
            );

            foreach ($subjects as $index => $subject) {
                $assignee = $developers->isNotEmpty() ? $developers[$index % $developers->count()] : null;
                $isClosed = $index === 0;

                $task = $project->tasks()->create([
                    'status_id' => $isClosed ? $closedStatusId : ($index % 2 === 0 ? $progressStatusId : $newStatusId),
                    'priority_id' => $index === 1 ? $highPriorityId : $normalPriorityId,
                    'author_id' => $pm->id,
                    'assigned_to' => $assignee?->id,
                    'subject' => $subject,
                    'description' => "Tarea generada automaticamente: {$subject} para el proyecto {$project->name}.",
                    'estimated_hours' => 8,
                    'spent_hours' => $isClosed ? 8 : 2,
                    'done_ratio' => $isClosed ? 100 : ($index % 2 === 0 ? 40 : 0),
                    'start_date' => now()->subWeeks(2),
                    'due_date' => now()->addWeeks(2),
                ]);

                if ($assignee) {
                    $task->comments()->create([
                        'user_id' => $assignee->id,
                        'comment' => 'Iniciando el trabajo en esta tarea.',
                    ]);

                    $task->timeEntries()->create([
                        'user_id' => $assignee->id,
                        'hours' => 2,
                        'comments' => 'Avance inicial.',
                        'spent_on' => now()->subDays(1)->toDateString(),
                    ]);
                }

                if ($qa && $index === 2) {
                    $task->comments()->create([
                        'user_id' => $qa->id,
                        'comment' => 'Quedo pendiente de pruebas de QA una vez completado el desarrollo.',
                    ]);
                }
            }
        }
    }
}
