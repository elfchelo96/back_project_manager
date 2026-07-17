<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponser;

    public function __construct(protected ProjectService $projectService)
    {
    }

    public function index(Request $request)
    {
        $projects = $this->projectService->paginate(
            $request->user(),
            $request->only(['status', 'search'])
        );

        return $this->paginated($projects, ProjectResource::class);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $this->success(new ProjectResource(
            $project->load(['owner', 'projectMembers.user', 'projectMembers.role', 'categories'])->loadCount('tasks')
        ));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->create($request->validated(), $request->user());

        return $this->created(new ProjectResource($project), 'Proyecto creado correctamente.');
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project = $this->projectService->update($project, $request->validated(), $request->user());

        return $this->success(new ProjectResource($project), 'Proyecto actualizado correctamente.');
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project, $request->user());

        return $this->noContentMessage('Proyecto eliminado correctamente.');
    }
}
