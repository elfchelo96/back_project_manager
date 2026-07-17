<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $pm = User::where('email', 'pedro.pm@empresa.com')->first();
        $admin = User::where('email', 'admin@empresa.com')->first();

        if (! $pm || ! $admin) {
            // Los seeders de usuarios deben ejecutarse antes que este.
            return;
        }

        $projects = [
            [
                'name' => 'Sistema de Facturacion Electronica',
                'identifier' => 'facturacion-sin',
                'description' => 'Modulo de facturacion electronica integrado con el SIN.',
                'owner_id' => $pm->id,
            ],
            [
                'name' => 'Portal de Atencion al Cliente',
                'identifier' => 'portal-clientes',
                'description' => 'Portal web para gestion de tickets y solicitudes de clientes.',
                'owner_id' => $pm->id,
            ],
            [
                'name' => 'Migracion a Infraestructura Cloud',
                'identifier' => 'migracion-cloud',
                'description' => 'Migracion de servidores on-premise a infraestructura cloud.',
                'owner_id' => $admin->id,
            ],
        ];

        $memberEmails = [
            'ana.admin@empresa.com', 'diego.dev@empresa.com', 'daniela.dev@empresa.com',
            'quentin.qa@empresa.com', 'carla.cliente@empresa.com',
        ];
        $members = User::whereIn('email', $memberEmails)->get();
        $developerRoleId = Role::where('name', 'Desarrollador')->value('id');

        foreach ($projects as $data) {
            $project = Project::firstOrCreate(['identifier' => $data['identifier']], [
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => 'active',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
                'owner_id' => $data['owner_id'],
            ]);

            $project->projectMembers()->firstOrCreate(['user_id' => $data['owner_id']]);

            foreach ($members as $member) {
                $project->projectMembers()->firstOrCreate(
                    ['user_id' => $member->id],
                    ['role_id' => $member->email === 'carla.cliente@empresa.com' ? null : $developerRoleId]
                );
            }

            $project->categories()->firstOrCreate(['name' => 'Backend'], ['project_id' => $project->id]);
            $project->categories()->firstOrCreate(['name' => 'Frontend'], ['project_id' => $project->id]);
            $project->categories()->firstOrCreate(['name' => 'Infraestructura'], ['project_id' => $project->id]);
        }
    }
}
