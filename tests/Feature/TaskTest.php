<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaskPrioritySeeder;
use Database\Seeders\TaskStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class, TaskStatusSeeder::class, TaskPrioritySeeder::class]);
    }

    protected function projectWithMember(User $user): Project
    {
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->projectMembers()->create(['user_id' => $user->id]);

        return $project;
    }

    public function test_an_authorized_user_can_create_a_task(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('Desarrollador');
        $project = $this->projectWithMember($developer);

        Sanctum::actingAs($developer);

        $response = $this->postJson('/api/tasks', [
            'project_id' => $project->id,
            'subject' => 'Implementar login',
            'description' => 'Pantalla de inicio de sesion.',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.subject', 'Implementar login');
        $this->assertDatabaseHas('tasks', ['subject' => 'Implementar login']);
    }

    public function test_changing_task_status_records_history(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Project Manager');
        $project = $this->projectWithMember($manager);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'author_id' => $manager->id,
            'status_id' => TaskStatus::where('name', 'Nuevo')->value('id'),
        ]);

        Sanctum::actingAs($manager);

        $newStatusId = TaskStatus::where('name', 'En Progreso')->value('id');

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'status_id' => $newStatusId,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_history', [
            'task_id' => $task->id,
            'field_changed' => 'status_id',
        ]);
    }

    public function test_a_task_cannot_be_closed_while_it_has_an_open_blocking_dependency(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Project Manager');
        $project = $this->projectWithMember($manager);

        $openStatusId = TaskStatus::where('name', 'Nuevo')->value('id');
        $closedStatusId = TaskStatus::where('name', 'Cerrado')->value('id');

        $blockingTask = Task::factory()->create([
            'project_id' => $project->id,
            'author_id' => $manager->id,
            'status_id' => $openStatusId,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'author_id' => $manager->id,
            'status_id' => $openStatusId,
        ]);

        $task->dependencies()->attach($blockingTask->id, ['type' => 'blocks']);

        Sanctum::actingAs($manager);

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'status_id' => $closedStatusId,
        ]);

        $response->assertStatus(422);
    }
}
