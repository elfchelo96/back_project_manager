<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    public function test_a_super_administrator_can_list_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Administrador');

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/roles');

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_a_regular_user_cannot_manage_roles(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('Desarrollador');

        Sanctum::actingAs($developer);

        $response = $this->getJson('/api/roles');

        $response->assertStatus(403);
    }

    public function test_a_super_administrator_can_create_a_role_with_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Administrador');

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/roles', [
            'name' => 'Auditor',
            'permissions' => ['reports.view'],
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'Auditor');
        $this->assertDatabaseHas('roles', ['name' => 'Auditor']);
    }
}
