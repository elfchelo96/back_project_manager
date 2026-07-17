<?php

namespace App\Repositories\Contracts;

use App\Models\WikiPage;

interface WikiPageRepositoryInterface extends BaseRepositoryInterface
{
    public function findInProject(int $projectId, string $titleOrId): ?WikiPage;
}
