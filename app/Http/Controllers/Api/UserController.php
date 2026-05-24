<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignRolesRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->list($request->only(['search', 'role', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
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

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->success(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(
            new UserResource($user->load('roles', 'permissions')),
            'User retrieved successfully'
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return $this->success(null, 'User deleted successfully');
    }

    public function assignRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->assignRoles($user, $request->validated('roles'));

        return $this->success(new UserResource($user), 'Roles assigned successfully');
    }
}
