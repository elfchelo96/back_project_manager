<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => $this->getModule(),
            'roles_count' => $this->when($this->roles_count !== null, $this->roles_count),
        ];
    }

    private function getModule(): string
    {
        return explode('.', $this->name)[0] ?? '';
    }
}
