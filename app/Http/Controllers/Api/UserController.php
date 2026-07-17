<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponser;

    public function __construct(protected UserService $userService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->paginate($request->only(['search', 'is_active', 'role']));

        return $this->paginated($users, UserResource::class);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return $this->success(new UserResource($user->load('roles')));
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated(), $request->user());

        return $this->created(new UserResource($user), 'Usuario creado correctamente.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->update($user, $request->validated(), $request->user());

        return $this->success(new UserResource($user), 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user, $request->user());

        return $this->noContentMessage('Usuario eliminado correctamente.');
    }

    public function roles(User $user)
    {
        $this->authorize('view', $user);

        return $this->success($user->roles()->pluck('name'));
    }

    public function permissions(User $user)
    {
        $this->authorize('view', $user);

        return $this->success($user->getAllPermissions()->pluck('name'));
    }
}
