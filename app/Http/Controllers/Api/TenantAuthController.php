<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Tenant\RegisterOwnerRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Services\TenantAuthService;
use App\Support\TenantContext;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TenantAuthService $tenantAuthService,
        private readonly TenantContext $tenant,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->tenantAuthService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name', 'api-token')
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'company' => new CompanyResource($result['company']),
            'domain' => $result['domain'],
        ], 'Login successful');
    }

    public function registerOwner(RegisterOwnerRequest $request): JsonResponse
    {
        $result = $this->tenantAuthService->registerOwner(
            $this->tenant->company(),
            $request->validated()
        );

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'company' => new CompanyResource($result['company']),
            'domain' => $result['domain'],
        ], 'Owner account created successfully', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->tenantAuthService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()->load('roles', 'permissions', 'company.domain')),
            'Current user retrieved'
        );
    }
}
