<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected ActivityLogService $activityLog,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($perPage, ['roles'], function ($query) use ($filters) {
            if (! empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            }

            if (! empty($filters['role'])) {
                $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
            }
        });
    }

    public function find(string $uuid): User
    {
        return User::with('roles')->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data, ?User $actor = null): User
    {
        $user = $this->users->create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        $this->activityLog->log($actor, 'users', 'create', "Usuario creado: {$user->email}");

        return $user->load('roles');
    }

    public function update(User $user, array $data, ?User $actor = null): User
    {
        $payload = array_filter([
            'firstname' => $data['firstname'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'username' => $data['username'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ], fn ($v) => $v !== null);

        if (array_key_exists('is_active', $data) && $data['is_active'] !== null) {
            $payload['is_active'] = $data['is_active'];
        }

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $this->users->update($user, $payload);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        $this->activityLog->log($actor, 'users', 'update', "Usuario actualizado: {$user->email}");

        return $user->refresh()->load('roles');
    }

    public function delete(User $user, ?User $actor = null): void
    {
        $this->users->delete($user);
        $this->activityLog->log($actor, 'users', 'delete', "Usuario eliminado: {$user->email}");
    }
}
