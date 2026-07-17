<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task' => $this->whenLoaded('task', fn () => [
                'id' => $this->task->uuid,
                'subject' => $this->task->subject,
            ]),
            'user' => new UserResource($this->whenLoaded('user')),
            'hours' => $this->hours,
            'comments' => $this->comments,
            'spent_on' => $this->spent_on,
            'created_at' => $this->created_at,
        ];
    }
}
