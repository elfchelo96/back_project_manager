<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ProjectService
{
    public function __construct(
        protected ProjectRepositoryInterface $projects,
        protected ActivityLogService $activityLog,
    ) {
    }

    public function paginate(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $isPrivileged = $user->hasRole(['Super Administrador', 'Administrador']);

        return $this->projects->paginate($perPage, ['owner'], function ($query) use ($user, $filters, $isPrivileged) {
            if (! $isPrivileged) {
                $query->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id)
                        ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
                });
            }

            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (! empty($filters['search'])) {
                $query->search($filters['search']);
            }
        });
    }

    public function find(string $uuid): Project
    {
        return Project::with(['owner', 'members', 'categories'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data, User $owner): Project
    {
        return DB::transaction(function () use ($data, $owner) {
            $project = $this->projects->create([
                'name' => $data['name'],
                'identifier' => $data['identifier'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'owner_id' => $owner->id,
            ]);

            // El propietario queda automaticamente como miembro del proyecto.
            $project->projectMembers()->create([
                'user_id' => $owner->id,
                'role_id' => null,
            ]);

            $this->activityLog->log($owner, 'projects', 'create', "Proyecto creado: {$project->name}");

            return $project->load(['owner', 'members']);
        });
    }

    public function update(Project $project, array $data, ?User $actor = null): Project
    {
        $payload = array_filter([
            'name' => $data['name'] ?? null,
            'identifier' => $data['identifier'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ], fn ($v) => $v !== null);

        $this->projects->update($project, $payload);

        $this->activityLog->log($actor, 'projects', 'update', "Proyecto actualizado: {$project->name}");

        return $project->refresh()->load(['owner', 'members']);
    }

    public function delete(Project $project, ?User $actor = null): void
    {
        $this->projects->delete($project);
        $this->activityLog->log($actor, 'projects', 'delete', "Proyecto eliminado: {$project->name}");
    }

    public function addMember(Project $project, int $userId, ?string $roleName = null, ?User $actor = null): void
    {
        $roleId = $roleName ? Role::where('name', $roleName)->value('id') : null;

        $project->projectMembers()->updateOrCreate(
            ['user_id' => $userId],
            ['role_id' => $roleId]
        );

        $this->activityLog->log($actor, 'projects', 'add_member', "Miembro agregado al proyecto {$project->name}");
    }

    public function updateMemberRole(Project $project, int $userId, ?string $roleName, ?User $actor = null): void
    {
        $roleId = $roleName ? Role::where('name', $roleName)->value('id') : null;

        $project->projectMembers()->where('user_id', $userId)->update(['role_id' => $roleId]);

        $this->activityLog->log($actor, 'projects', 'update_member_role', "Rol de miembro actualizado en {$project->name}");
    }

    public function removeMember(Project $project, int $userId, ?User $actor = null): void
    {
        $project->projectMembers()->where('user_id', $userId)->delete();
        $this->activityLog->log($actor, 'projects', 'remove_member', "Miembro removido del proyecto {$project->name}");
    }
}
