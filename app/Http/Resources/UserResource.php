<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->whenLoaded('roles', fn () => RoleResource::collection($this->roles)),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'tenant_domain' => $this->whenLoaded('company', fn () => $this->company?->domain?->domain),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
