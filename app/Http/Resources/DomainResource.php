<?php

namespace App\Http\Resources;

use App\Services\DomainSlugService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $slugService = app(DomainSlugService::class);

        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'url' => $slugService->buildUrl($this->domain),
            'company_id' => $this->company_id,
        ];
    }
}
