<?php

namespace App\Repositories\Eloquent;

use App\Models\WikiPage;
use App\Repositories\Contracts\WikiPageRepositoryInterface;

class EloquentWikiPageRepository extends BaseRepository implements WikiPageRepositoryInterface
{
    public function __construct(WikiPage $model)
    {
        parent::__construct($model);
    }

    /**
     * Busca una pagina wiki dentro de un proyecto por su id numerico
     * o, si no es numerico, por una coincidencia exacta de titulo.
     */
    public function findInProject(int $projectId, string $titleOrId): ?WikiPage
    {
        $query = $this->model->where('project_id', $projectId);

        if (ctype_digit($titleOrId)) {
            return $query->find((int) $titleOrId);
        }

        return $query->where('title', $titleOrId)->first();
    }
}
