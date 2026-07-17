<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskStatusResource;
use App\Models\TaskStatus;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(TaskStatusResource::collection(TaskStatus::ordered()->get()));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:task_statuses,name'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_closed' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $status = TaskStatus::create($data);

        return $this->created(new TaskStatusResource($status), 'Estado creado correctamente.');
    }

    public function update(Request $request, TaskStatus $status)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:50', 'unique:task_statuses,name,'.$status->id],
            'color' => ['nullable', 'string', 'max:20'],
            'is_closed' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $status->update($data);

        return $this->success(new TaskStatusResource($status), 'Estado actualizado correctamente.');
    }

    public function destroy(TaskStatus $status)
    {
        $status->delete();

        return $this->noContentMessage('Estado eliminado correctamente.');
    }
}
