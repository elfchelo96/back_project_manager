<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentTaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Construye un query filtrado segun los parametros recibidos
     * (tipicamente desde el request del index del controlador).
     *
     * Filtros soportados: project_id, status_id, priority_id, category_id,
     * assigned_to, author_id, parent_id, only_roots, overdue, search.
     */
    public function filtered(array $filters): Builder
    {
        $query = $this->query();

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (! empty($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (array_key_exists('parent_id', $filters) && $filters['parent_id'] !== null) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (! empty($filters['only_roots'])) {
            $query->whereNull('parent_id');
        }

        if (! empty($filters['overdue'])) {
            $query->overdue();
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query;
    }
}
