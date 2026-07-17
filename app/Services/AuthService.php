<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected ActivityLogService $activityLog,
    ) {
    }

    /**
     * Autentica por email o username + password y emite un token Sanctum.
     *
     * @return array{user: User, token: string}
     */
    public function login(string $login, string $password, ?string $deviceName = null): array
    {
        $user = $this->users->findByEmailOrUsername($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Su cuenta se encuentra desactivada. Contacte al administrador.'],
            ]);
        }

        $user->markAsLoggedIn();

        $token = $user->createToken($deviceName ?: 'api-token')->plainTextToken;

        // Log de actividad en background para no bloquear la respuesta
        dispatch(function () use ($user) {
            $this->activityLog->log($user, 'auth', 'login', "Inicio de sesion de {$user->email}");
        });

        return ['user' => $user, 'token' => $token];
    }

    public function register(array $data): array
    {
        $user = $this->users->create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        // Rol por defecto para auto-registro publico: el administrador puede
        // reasignar un rol con mayores privilegios posteriormente.
        $user->assignRole('Invitado');

        $token = $user->createToken('api-token')->plainTextToken;

        dispatch(function () use ($user) {
            $this->activityLog->log($user, 'auth', 'register', "Registro de nuevo usuario {$user->email}");
        });

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        dispatch(function () use ($user) {
            $this->activityLog->log($user, 'auth', 'logout', "Cierre de sesion de {$user->email}");
        });
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
        dispatch(function () use ($user) {
            $this->activityLog->log($user, 'auth', 'logout_all', "Cierre de todas las sesiones de {$user->email}");
        });
    }

    public function refresh(User $user, ?string $deviceName = null): string
    {
        $current = $user->currentAccessToken();

        $newToken = $user->createToken($deviceName ?: 'api-token')->plainTextToken;

        if ($current) {
            $current->delete();
        }

        return $newToken;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contrasena actual no es correcta.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($newPassword)])->save();
        $user->tokens()->delete();

        dispatch(function () use ($user) {
            $this->activityLog->log($user, 'auth', 'change_password', "Cambio de contrasena de {$user->email}");
        });
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user) use ($data) {
                $user->forceFill(['password' => Hash::make($data['password'])])->save();
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        return $status;
    }

    public function generateEmailVerificationToken(User $user): string
    {
        return Str::random(60);
    }
}
