<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Permission::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update([
            'name' => $data['name'] ?? $permission->name,
        ]);

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
