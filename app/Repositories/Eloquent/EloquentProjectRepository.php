<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function findByIdentifier(string $identifier): ?Project
    {
        return $this->model->where('identifier', $identifier)->first();
    }

    /**
     * Proyectos visibles para un usuario: los que posee o de los que es miembro.
     */
    public function forUser(int $userId): Builder
    {
        return $this->model->where('owner_id', $userId)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $userId));
    }
}
