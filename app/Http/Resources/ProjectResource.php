<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'identifier' => $this->identifier,
            'description' => $this->description,
            'status' => $this->status?->value ?? $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members' => ProjectMemberResource::collection($this->whenLoaded('projectMembers')),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
