<?php

namespace App\Repositories\Contracts;

use App\Models\Project;

interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?Project;

    public function forUser(int $userId): \Illuminate\Database\Eloquent\Builder;
}
