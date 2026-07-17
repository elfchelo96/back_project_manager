<?php

namespace App\Repositories\Contracts;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function filtered(array $filters): \Illuminate\Database\Eloquent\Builder;
}
