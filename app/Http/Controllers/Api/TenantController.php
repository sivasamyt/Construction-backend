<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Support\TenantContext;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TenantContext $tenant) {}

    public function show(): JsonResponse
    {
        $company = $this->tenant->company()?->load('domain');
// dd($company, $this->tenant->domainName());
        return $this->success([
            'company' => new CompanyResource($company),
            'domain' => $this->tenant->domainName(),
            'has_owner' => $company?->hasOwner() ?? false,
        ], 'Company retrieved successfully');
    }
}
