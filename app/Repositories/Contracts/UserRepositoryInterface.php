<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmailOrUsername(string $login): ?User;
}
