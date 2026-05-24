<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Role::query()->with('permissions');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        if (! empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        if (isset($data['name'])) {
            $role->name = $data['name'];
            $role->save();
        }

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }
}
