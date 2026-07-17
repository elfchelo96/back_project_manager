<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentService
{
    public function paginate(Task $task)
    {
        return $task->attachments()->with('user')->latest('id')->get();
    }

    public function upload(Task $task, User $user, UploadedFile $file): TaskAttachment
    {
        $path = $file->store('attachments/'.$task->id, 'public');

        return $task->attachments()->create([
            'user_id' => $user->id,
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
            'mime_type' => $file->getClientMimeType(),
            'filesize' => $file->getSize(),
        ]);
    }

    public function delete(TaskAttachment $attachment): void
    {
        if (Storage::disk('public')->exists($attachment->filepath)) {
            Storage::disk('public')->delete($attachment->filepath);
        }

        $attachment->delete();
    }
}
