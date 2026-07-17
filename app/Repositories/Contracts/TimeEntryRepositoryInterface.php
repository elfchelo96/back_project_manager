<?php

namespace App\Repositories\Contracts;

interface TimeEntryRepositoryInterface extends BaseRepositoryInterface
{
    public function totalHoursForTask(int $taskId): float;
}
