<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            // ->whereNull('company_id')
            ->with('roles', 'permissions');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, User $actor): User
    {
        if (! empty($data['roles'])) {
            $this->assertCanAssignPlatformRoles($actor, $data['roles']);
        }

        $user = User::create([
            'company_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles', 'permissions');
    }

    public function update(User $user, array $data, User $actor): User
    {
        if (array_key_exists('roles', $data)) {
            $this->assertCanAssignPlatformRoles($actor, $data['roles'] ?? [], $user);
        }

        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles', 'permissions');
    }

    public function delete(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    public function assignRoles(User $user, array $roleNames, User $actor): User
    {
        $this->assertCanAssignPlatformRoles($actor, $roleNames, $user);

        $roles = Role::whereIn('name', $roleNames)->get();
        $user->syncRoles($roles);

        return $user->load('roles', 'permissions');
    }

    private function assertCanAssignPlatformRoles(User $actor, array $roles, ?User $target = null): void
    {
        if ($actor->hasRole(RoleName::SuperAdmin->value)) {
            return;
        }

        if ($actor->hasRole(RoleName::Admin->value)) {
            $forbidden = array_intersect($roles, RoleName::privileged());

            if ($forbidden !== []) {
                throw ValidationException::withMessages([
                    'roles' => ['Admins cannot assign super_admin or admin roles.'],
                ]);
            }

            if ($target?->hasAnyRole(RoleName::privileged())) {
                throw ValidationException::withMessages([
                    'roles' => ['Admins cannot modify roles for super_admin or admin users.'],
                ]);
            }

            return;
        }

        throw ValidationException::withMessages([
            'roles' => ['You do not have permission to assign roles.'],
        ]);
    }
}
