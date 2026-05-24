<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PermissionService $permissionService) {}

    public function index(Request $request): JsonResponse
    {
        $permissions = $this->permissionService->list($request->only(['search', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Permissions retrieved successfully',
            'data' => PermissionResource::collection($permissions->items()),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
                'from' => $permissions->firstItem(),
                'to' => $permissions->lastItem(),
            ],
            'links' => [
                'first' => $permissions->url(1),
                'last' => $permissions->url($permissions->lastPage()),
                'prev' => $permissions->previousPageUrl(),
                'next' => $permissions->nextPageUrl(),
            ],
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create($request->validated());

        return $this->success(new PermissionResource($permission), 'Permission created successfully', 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        return $this->success(new PermissionResource($permission), 'Permission retrieved successfully');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->permissionService->update($permission, $request->validated());

        return $this->success(new PermissionResource($permission), 'Permission updated successfully');
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->permissionService->delete($permission);

        return $this->success(null, 'Permission deleted successfully');
    }
}
