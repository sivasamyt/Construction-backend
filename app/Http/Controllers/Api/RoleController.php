<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->list($request->only(['search', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Roles retrieved successfully',
            'data' => RoleResource::collection($roles->items()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ],
            'links' => [
                'first' => $roles->url(1),
                'last' => $roles->url($roles->lastPage()),
                'prev' => $roles->previousPageUrl(),
                'next' => $roles->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->success(new RoleResource($role), 'Role created successfully', 201);
    }

    public function show(Role $role): JsonResponse
    {
        return $this->success(
            new RoleResource($role->load('permissions')),
            'Role retrieved successfully'
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return $this->success(new RoleResource($role), 'Role updated successfully');
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return $this->success(null, 'Role deleted successfully');
    }

    public function syncPermissions(SyncPermissionsRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->syncPermissions($role, $request->validated('permissions'));

        return $this->success(new RoleResource($role), 'Permissions synced successfully');
    }
}
