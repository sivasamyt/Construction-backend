<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_number' => $this->contact_number,
            'email' => $this->email,
            'address' => $this->address,
            'domain' => $this->whenLoaded('domain', fn () => new DomainResource($this->domain)),
            'has_owner' => $this->when(
                $this->relationLoaded('users') || isset($this->has_owner),
                fn () => $this->has_owner ?? $this->hasOwner()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
