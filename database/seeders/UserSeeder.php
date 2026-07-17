<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@empresa.com')],
            [
                'firstname' => 'Administrador',
                'lastname' => 'General',
                'username' => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin123*')),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Super Administrador']);

        // Un usuario de ejemplo por cada rol restante, con password uniforme
        // para facilitar las pruebas manuales (ver README para credenciales).
        $sampleUsers = [
            ['role' => 'Administrador', 'firstname' => 'Ana', 'lastname' => 'Administradora', 'username' => 'ana.admin', 'email' => 'ana.admin@empresa.com'],
            ['role' => 'Project Manager', 'firstname' => 'Pedro', 'lastname' => 'Gomez', 'username' => 'pedro.pm', 'email' => 'pedro.pm@empresa.com'],
            ['role' => 'Desarrollador', 'firstname' => 'Diego', 'lastname' => 'Lopez', 'username' => 'diego.dev', 'email' => 'diego.dev@empresa.com'],
            ['role' => 'Desarrollador', 'firstname' => 'Daniela', 'lastname' => 'Mamani', 'username' => 'daniela.dev', 'email' => 'daniela.dev@empresa.com'],
            ['role' => 'QA', 'firstname' => 'Quentin', 'lastname' => 'Vargas', 'username' => 'quentin.qa', 'email' => 'quentin.qa@empresa.com'],
            ['role' => 'Cliente', 'firstname' => 'Carla', 'lastname' => 'Flores', 'username' => 'carla.cliente', 'email' => 'carla.cliente@empresa.com'],
            ['role' => 'Invitado', 'firstname' => 'Ivan', 'lastname' => 'Invitado', 'username' => 'ivan.guest', 'email' => 'ivan.guest@empresa.com'],
        ];

        foreach ($sampleUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'username' => $data['username'],
                    'password' => Hash::make('Password123*'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
