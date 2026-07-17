<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct(protected AuthService $authService)
    {
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->validated('login'),
            $request->validated('password'),
            $request->validated('device_name')
        );

        return $this->success([
            'user' => new UserResource($result['user']->load('roles')),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Inicio de sesion exitoso.');
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->created([
            'user' => new UserResource($result['user']->load('roles')),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registro exitoso.');
    }

    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()->load('roles', 'permissions')));
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->noContentMessage('Sesion cerrada correctamente.');
    }

    public function logoutAll(Request $request)
    {
        $this->authService->logoutAll($request->user());

        return $this->noContentMessage('Todas las sesiones fueron cerradas.');
    }

    public function refresh(Request $request)
    {
        $token = $this->authService->refresh($request->user());

        return $this->success(['token' => $token, 'token_type' => 'Bearer'], 'Token renovado correctamente.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password')
        );

        return $this->noContentMessage('Contrasena actualizada correctamente.');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->authService->sendPasswordResetLink($request->validated('email'));

        return $this->noContentMessage('Si el correo existe, se enviaron las instrucciones de recuperacion.');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return $this->error('No se pudo restablecer la contrasena. El token puede ser invalido o haber expirado.', 422);
        }

        return $this->noContentMessage('Contrasena restablecida correctamente.');
    }
}
