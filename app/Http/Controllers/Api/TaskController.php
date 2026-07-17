<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskHistoryResource;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponser;

    public function __construct(protected TaskService $taskService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->paginate($request->only([
            'project_id', 'status_id', 'priority_id', 'category_id',
            'assigned_to', 'author_id', 'parent_id', 'only_roots', 'overdue', 'search',
        ]));

        return $this->paginated($tasks, TaskResource::class);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return $this->success(new TaskResource($task->load([
            'status', 'priority', 'category', 'author', 'assignee',
            'project', 'parent', 'children', 'dependencies', 'dependents',
        ])));
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->create($request->validated(), $request->user());

        return $this->created(new TaskResource($task), 'Tarea creada correctamente.');
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task = $this->taskService->update($task, $request->validated(), $request->user());

        return $this->success(new TaskResource($task), 'Tarea actualizada correctamente.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task, request()->user());

        return $this->noContentMessage('Tarea eliminada correctamente.');
    }

    public function history(Task $task)
    {
        $this->authorize('view', $task);

        $history = $this->taskService->history($task);

        return $this->paginated($history, TaskHistoryResource::class);
    }

    public function addDependency(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'depends_on_task_id' => ['required', 'integer', 'exists:tasks,id'],
            'type' => ['nullable', 'in:blocks,relates_to'],
        ]);

        $this->taskService->addDependency($task, $request->integer('depends_on_task_id'), $request->input('type', 'blocks'));

        return $this->success(new TaskResource($task->load('dependencies')), 'Dependencia agregada correctamente.');
    }

    public function removeDependency(Task $task, int $dependsOnTaskId)
    {
        $this->authorize('update', $task);

        $this->taskService->removeDependency($task, $dependsOnTaskId);

        return $this->noContentMessage('Dependencia removida correctamente.');
    }
}
