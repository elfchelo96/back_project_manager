<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_system' => $this->isSystemRole(),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')),
            'permissions_count' => $this->when($this->permissions_count !== null, $this->permissions_count),
            'users_count' => $this->when($this->users_count !== null, $this->users_count),
            'created_at' => $this->created_at,
        ];
    }

    private function isSystemRole(): bool
    {
        return in_array($this->name, ['Super Administrador', 'Administrador'], true);
    }
}
