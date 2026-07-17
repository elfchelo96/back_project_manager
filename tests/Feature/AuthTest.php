<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    }

    public function test_a_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'firstname' => 'Juan',
            'lastname' => 'Perez',
            'username' => 'juan.perez',
            'email' => 'juan.perez@example.com',
            'password' => 'Password123*',
            'password_confirmation' => 'Password123*',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'juan.perez@example.com');

        $this->assertDatabaseHas('users', ['email' => 'juan.perez@example.com']);
    }

    public function test_a_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Secret123*'),
        ]);
        $user->assignRole('Invitado');

        $response = $this->postJson('/api/auth/login', [
            'login' => 'login@example.com',
            'password' => 'Secret123*',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token', 'token_type']]);
    }

    public function test_login_fails_with_incorrect_credentials(): void
    {
        User::factory()->create(['email' => 'wrong@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'wrong@example.com',
            'password' => 'incorrect-password',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Invitado');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Invitado');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)->assertJsonPath('success', true);
    }
}
