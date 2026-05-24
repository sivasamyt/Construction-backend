<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantUserRequest;
use App\Http\Requests\Tenant\UpdateTenantUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\TenantUserService;
use App\Support\TenantContext;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantUserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TenantUserService $tenantUserService,
        private readonly TenantContext $tenant,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->tenantUserService->list($request->only(['search', 'role', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Company users retrieved successfully',
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreTenantUserRequest $request): JsonResponse
    {
        $user = $this->tenantUserService->create($request->validated());

        return $this->success(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user): JsonResponse
    {
        if (! $user->belongsToCompany($this->tenant->companyId())) {
            return $this->error('User not found', 404);
        }

        return $this->success(
            new UserResource($user->load('roles', 'permissions')),
            'User retrieved successfully'
        );
    }

    public function update(UpdateTenantUserRequest $request, User $user): JsonResponse
    {
        $user = $this->tenantUserService->update($user, $request->validated());

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->tenantUserService->delete($user);

        return $this->success(null, 'User deleted successfully');
    }
}
