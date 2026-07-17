<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskComment\StoreTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\TaskCommentService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    use ApiResponser;

    public function __construct(protected TaskCommentService $commentService)
    {
    }

    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $comments = $this->commentService->paginate($task);

        return $this->paginated($comments, TaskCommentResource::class);
    }

    public function store(StoreTaskCommentRequest $request, Task $task)
    {
        $this->authorize('view', $task);

        $comment = $this->commentService->create($task, $request->user(), $request->validated('comment'));

        return $this->created(new TaskCommentResource($comment), 'Comentario agregado correctamente.');
    }

    public function update(StoreTaskCommentRequest $request, Task $task, TaskComment $comment)
    {
        $this->authorize('update', $comment);

        $comment = $this->commentService->update($comment, $request->validated('comment'));

        return $this->success(new TaskCommentResource($comment), 'Comentario actualizado correctamente.');
    }

    public function destroy(Task $task, TaskComment $comment)
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return $this->noContentMessage('Comentario eliminado correctamente.');
    }
}
