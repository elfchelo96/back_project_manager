<?php

namespace App\Repositories\Eloquent;

use App\Models\TimeEntry;
use App\Repositories\Contracts\TimeEntryRepositoryInterface;

class EloquentTimeEntryRepository extends BaseRepository implements TimeEntryRepositoryInterface
{
    public function __construct(TimeEntry $model)
    {
        parent::__construct($model);
    }

    public function totalHoursForTask(int $taskId): float
    {
        return (float) $this->model->where('task_id', $taskId)->sum('hours');
    }
}
