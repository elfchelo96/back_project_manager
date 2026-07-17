<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'module' => $this->module,
            'action' => $this->action,
            'description' => $this->description,
            'ip' => $this->ip,
            'created_at' => $this->created_at,
        ];
    }
}
