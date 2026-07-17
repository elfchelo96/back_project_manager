<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskAttachment\StoreTaskAttachmentRequest;
use App\Http\Resources\TaskAttachmentResource;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskAttachmentService;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    use ApiResponser;

    public function __construct(protected TaskAttachmentService $attachmentService)
    {
    }

    public function index(Task $task)
    {
        $this->authorize('view', $task);

        return $this->success(TaskAttachmentResource::collection($this->attachmentService->paginate($task)));
    }

    public function store(StoreTaskAttachmentRequest $request, Task $task)
    {
        $this->authorize('view', $task);

        $attachment = $this->attachmentService->upload($task, $request->user(), $request->file('file'));

        return $this->created(new TaskAttachmentResource($attachment), 'Archivo adjuntado correctamente.');
    }

    public function download(Task $task, TaskAttachment $attachment)
    {
        $this->authorize('view', $task);

        if (! Storage::disk('public')->exists($attachment->filepath)) {
            return $this->error('El archivo ya no existe en el almacenamiento.', 404);
        }

        return Storage::disk('public')->download($attachment->filepath, $attachment->filename);
    }

    public function destroy(Task $task, TaskAttachment $attachment)
    {
        $this->authorize('update', $task);

        $this->attachmentService->delete($attachment);

        return $this->noContentMessage('Archivo eliminado correctamente.');
    }
}
