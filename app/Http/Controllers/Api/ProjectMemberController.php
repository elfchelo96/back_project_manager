<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    use ApiResponser;

    public function __construct(protected ProjectService $projectService)
    {
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $members = $project->projectMembers()->with(['user', 'role'])->get();

        return $this->success(ProjectMemberResource::collection($members));
    }

    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $this->projectService->addMember(
            $project,
            $request->validated('user_id'),
            $request->validated('role'),
            $request->user()
        );

        return $this->created(
            ProjectMemberResource::collection($project->projectMembers()->with(['user', 'role'])->get()),
            'Miembro agregado al proyecto.'
        );
    }

    public function update(StoreProjectMemberRequest $request, Project $project)
    {
        $this->projectService->updateMemberRole(
            $project,
            $request->validated('user_id'),
            $request->validated('role'),
            $request->user()
        );

        return $this->success(
            ProjectMemberResource::collection($project->projectMembers()->with(['user', 'role'])->get()),
            'Rol del miembro actualizado.'
        );
    }

    public function destroy(Request $request, Project $project, int $userId)
    {
        $this->authorize('manageMembers', $project);

        $this->projectService->removeMember($project, $userId, $request->user());

        return $this->noContentMessage('Miembro removido del proyecto.');
    }
}
