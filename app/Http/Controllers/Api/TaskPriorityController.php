<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskPriorityResource;
use App\Models\TaskPriority;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TaskPriorityController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(TaskPriorityResource::collection(TaskPriority::ordered()->get()));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:task_priorities,name'],
            'level' => ['nullable', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $priority = TaskPriority::create($data);

        return $this->created(new TaskPriorityResource($priority), 'Prioridad creada correctamente.');
    }

    public function update(Request $request, TaskPriority $priority)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:50', 'unique:task_priorities,name,'.$priority->id],
            'level' => ['nullable', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $priority->update($data);

        return $this->success(new TaskPriorityResource($priority), 'Prioridad actualizada correctamente.');
    }

    public function destroy(TaskPriority $priority)
    {
        $priority->delete();

        return $this->noContentMessage('Prioridad eliminada correctamente.');
    }
}
