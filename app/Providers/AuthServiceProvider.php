<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\WikiPage;
use App\Policies\ProjectPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Policies\WikiPagePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapeo explicito Modelo => Policy.
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        TaskComment::class => TaskCommentPolicy::class,
        User::class => UserPolicy::class,
        WikiPage::class => WikiPagePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super Administrador y Administrador omiten todas las verificaciones
        // de Policy/Gate (pero las rutas siguen protegidas por permisos).
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('Super Administrador')) {
                return true;
            }

            return null;
        });
    }
}
