<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project->uuid,
                'name' => $this->project->name,
            ]),
            'category' => new TaskCategoryResource($this->whenLoaded('category')),
            'status' => new TaskStatusResource($this->whenLoaded('status')),
            'priority' => new TaskPriorityResource($this->whenLoaded('priority')),
            'author' => new UserResource($this->whenLoaded('author')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'parent_id' => $this->when($this->parent_id !== null, fn () => $this->parent?->uuid),
            'subtasks' => TaskResource::collection($this->whenLoaded('children')),
            'dependencies' => $this->whenLoaded('dependencies', function () {
                return $this->dependencies->map(fn ($task) => [
                    'id' => $task->uuid,
                    'subject' => $task->subject,
                    'type' => $task->pivot->type,
                ]);
            }),
            'subject' => $this->subject,
            'description' => $this->description,
            'estimated_hours' => $this->estimated_hours,
            'spent_hours' => $this->spent_hours,
            'done_ratio' => $this->done_ratio,
            'is_overdue' => $this->is_overdue,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
