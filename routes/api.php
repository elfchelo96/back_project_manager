<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaskAttachmentController;
use App\Http\Controllers\Api\TaskCategoryController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskPriorityController;
use App\Http\Controllers\Api\TaskStatusController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WikiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas publicas de autenticacion
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (Sanctum) + verificacion de cuenta activa
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // ---- Auth (sesion) ----
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // ---- Dashboard ----
    Route::get('dashboard', [DashboardController::class, 'index']);

    // ---- Usuarios ----
    Route::middleware('permission:users.view')->get('users', [UserController::class, 'index']);
    Route::middleware('permission:users.create')->post('users', [UserController::class, 'store']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:users.delete')->delete('users/{user}', [UserController::class, 'destroy']);

    // ---- Roles y permisos ----
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::get('roles/{role}', [RoleController::class, 'show']);
        Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);
        Route::get('roles/{role}/users', [RoleController::class, 'users']);
        Route::post('roles/{role}/users', [RoleController::class, 'assignUser']);
        Route::delete('roles/{role}/users/{user}', [RoleController::class, 'revokeUser']);
    });
    Route::middleware('permission:permissions.manage')->group(function () {
        Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::post('roles/{role}/permissions', [RoleController::class, 'addPermissions']);
        Route::delete('roles/{role}/permissions', [RoleController::class, 'removePermissions']);
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::match(['put', 'patch'], 'permissions/{permission}', [PermissionController::class, 'update']);
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);
    });
    Route::middleware('permission:roles.manage|permissions.manage')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
    });
    Route::get('users/{user}/roles', [UserController::class, 'roles']);
    Route::get('users/{user}/permissions', [UserController::class, 'permissions']);

    // ---- Proyectos ----
    Route::middleware('permission:projects.view')->get('projects', [ProjectController::class, 'index']);
    Route::middleware('permission:projects.create')->post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::middleware('permission:projects.edit')->match(['put', 'patch'], 'projects/{project}', [ProjectController::class, 'update']);
    Route::middleware('permission:projects.delete')->delete('projects/{project}', [ProjectController::class, 'destroy']);

    // ---- Miembros de proyecto (anidado) ----
    Route::get('projects/{project}/members', [ProjectMemberController::class, 'index']);
    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store']);
    Route::match(['put', 'patch'], 'projects/{project}/members', [ProjectMemberController::class, 'update']);
    Route::delete('projects/{project}/members/{userId}', [ProjectMemberController::class, 'destroy']);

    // ---- Categorias de tareas (anidado por proyecto) ----
    Route::get('projects/{project}/categories', [TaskCategoryController::class, 'index']);
    Route::post('projects/{project}/categories', [TaskCategoryController::class, 'store']);
    Route::match(['put', 'patch'], 'projects/{project}/categories/{category}', [TaskCategoryController::class, 'update']);
    Route::delete('projects/{project}/categories/{category}', [TaskCategoryController::class, 'destroy']);

    // ---- Wiki (anidado por proyecto) ----
    Route::get('projects/{project}/wiki', [WikiController::class, 'index']);
    Route::post('projects/{project}/wiki', [WikiController::class, 'store']);
    Route::get('projects/{project}/wiki/{wikiPage}', [WikiController::class, 'show']);
    Route::match(['put', 'patch'], 'projects/{project}/wiki/{wikiPage}', [WikiController::class, 'update']);
    Route::delete('projects/{project}/wiki/{wikiPage}', [WikiController::class, 'destroy']);

    // ---- Catalogos: estados y prioridades de tareas ----
    Route::get('task-statuses', [TaskStatusController::class, 'index']);
    Route::get('task-priorities', [TaskPriorityController::class, 'index']);
    Route::middleware('role:Super Administrador|Administrador')->group(function () {
        Route::post('task-statuses', [TaskStatusController::class, 'store']);
        Route::match(['put', 'patch'], 'task-statuses/{status}', [TaskStatusController::class, 'update']);
        Route::delete('task-statuses/{status}', [TaskStatusController::class, 'destroy']);

        Route::post('task-priorities', [TaskPriorityController::class, 'store']);
        Route::match(['put', 'patch'], 'task-priorities/{priority}', [TaskPriorityController::class, 'update']);
        Route::delete('task-priorities/{priority}', [TaskPriorityController::class, 'destroy']);
    });

    // ---- Tareas ----
    Route::middleware('permission:tasks.view')->get('tasks', [TaskController::class, 'index']);
    Route::middleware('permission:tasks.create')->post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{task}', [TaskController::class, 'show']);
    Route::middleware('permission:tasks.edit')->match(['put', 'patch'], 'tasks/{task}', [TaskController::class, 'update']);
    Route::middleware('permission:tasks.delete')->delete('tasks/{task}', [TaskController::class, 'destroy']);
    Route::get('tasks/{task}/history', [TaskController::class, 'history']);
    Route::post('tasks/{task}/dependencies', [TaskController::class, 'addDependency']);
    Route::delete('tasks/{task}/dependencies/{dependsOnTaskId}', [TaskController::class, 'removeDependency']);

    // ---- Comentarios de tareas (anidado) ----
    Route::get('tasks/{task}/comments', [TaskCommentController::class, 'index']);
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store']);
    Route::match(['put', 'patch'], 'tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update']);
    Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy']);

    // ---- Adjuntos de tareas (anidado) ----
    Route::get('tasks/{task}/attachments', [TaskAttachmentController::class, 'index']);
    Route::post('tasks/{task}/attachments', [TaskAttachmentController::class, 'store']);
    Route::get('tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download']);
    Route::delete('tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy']);

    // ---- Registro de horas (time tracking) ----
    Route::middleware('permission:time.manage')->group(function () {
        Route::get('time-entries', [TimeEntryController::class, 'index']);
        Route::get('tasks/{task}/time-entries', [TimeEntryController::class, 'indexForTask']);
        Route::post('tasks/{task}/time-entries', [TimeEntryController::class, 'storeForTask']);
        Route::match(['put', 'patch'], 'time-entries/{timeEntry}', [TimeEntryController::class, 'update']);
        Route::delete('time-entries/{timeEntry}', [TimeEntryController::class, 'destroy']);
    });

    // ---- Notificaciones ----
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
    });

    // ---- Reportes ----
    Route::middleware('permission:reports.view')->prefix('reports')->group(function () {
        Route::get('tasks-by-status', [ReportController::class, 'tasksByStatus']);
        Route::get('tasks-by-user', [ReportController::class, 'tasksByUser']);
        Route::get('hours-worked', [ReportController::class, 'hoursWorked']);
        Route::get('productivity', [ReportController::class, 'productivity']);
        Route::get('active-projects', [ReportController::class, 'activeProjects']);
        Route::get('finished-projects', [ReportController::class, 'finishedProjects']);
    });
});
