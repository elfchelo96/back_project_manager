<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    public function test_a_user_with_permission_can_create_a_project(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Project Manager');

        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/projects', [
            'name' => 'Proyecto de prueba',
            'identifier' => 'proyecto-prueba',
            'description' => 'Descripcion de prueba.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.identifier', 'proyecto-prueba');

        $this->assertDatabaseHas('projects', ['identifier' => 'proyecto-prueba']);
    }

    public function test_a_user_without_permission_cannot_create_a_project(): void
    {
        $guest = User::factory()->create();
        $guest->assignRole('Invitado');

        Sanctum::actingAs($guest);

        $response = $this->postJson('/api/projects', [
            'name' => 'Proyecto no autorizado',
            'identifier' => 'proyecto-no-autorizado',
        ]);

        $response->assertStatus(403);
    }

    public function test_the_owner_can_view_their_own_project(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Project Manager');

        $project = Project::factory()->create(['owner_id' => $manager->id]);
        $project->projectMembers()->create(['user_id' => $manager->id]);

        Sanctum::actingAs($manager);

        $response = $this->getJson("/api/projects/{$project->uuid}");

        $response->assertStatus(200)->assertJsonPath('data.identifier', $project->identifier);
    }

    public function test_a_non_privileged_user_only_sees_projects_they_belong_to(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Project Manager');

        $outsider = User::factory()->create();
        $outsider->assignRole('Desarrollador');

        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->projectMembers()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($outsider);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }
}
