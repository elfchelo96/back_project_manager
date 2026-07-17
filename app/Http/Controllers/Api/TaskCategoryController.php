<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskCategory\StoreTaskCategoryRequest;
use App\Http\Requests\TaskCategory\UpdateTaskCategoryRequest;
use App\Http\Resources\TaskCategoryResource;
use App\Models\Project;
use App\Models\TaskCategory;
use App\Traits\ApiResponser;

class TaskCategoryController extends Controller
{
    use ApiResponser;

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return $this->success(TaskCategoryResource::collection($project->categories));
    }

    public function store(StoreTaskCategoryRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $category = $project->categories()->create($request->validated());

        return $this->created(new TaskCategoryResource($category), 'Categoria creada correctamente.');
    }

    public function update(UpdateTaskCategoryRequest $request, Project $project, TaskCategory $category)
    {
        $this->authorize('update', $project);

        $category->update($request->validated());

        return $this->success(new TaskCategoryResource($category), 'Categoria actualizada correctamente.');
    }

    public function destroy(Project $project, TaskCategory $category)
    {
        $this->authorize('update', $project);

        $category->delete();

        return $this->noContentMessage('Categoria eliminada correctamente.');
    }
}
