<?php

namespace App\Providers;

use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TimeEntryRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WikiPageRepositoryInterface;
use App\Repositories\Eloquent\EloquentProjectRepository;
use App\Repositories\Eloquent\EloquentTaskRepository;
use App\Repositories\Eloquent\EloquentTimeEntryRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentWikiPageRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Mapeo Interface => Implementacion concreta.
     * Permite inyectar los contratos en los Services sin acoplarlos a Eloquent.
     */
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
        ProjectRepositoryInterface::class => EloquentProjectRepository::class,
        TaskRepositoryInterface::class => EloquentTaskRepository::class,
        TimeEntryRepositoryInterface::class => EloquentTimeEntryRepository::class,
        WikiPageRepositoryInterface::class => EloquentWikiPageRepository::class,
    ];

    public function register(): void
    {
        //
    }
}
