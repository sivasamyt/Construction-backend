<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = Permission::pluck('name')->toArray();

        $companyUserPermissions = [
            'dashboard.view',
            'company.users.view',
            'company.users.create',
            'company.users.update',
            'company.users.delete',
        ];

        $rolePermissions = [
            RoleName::SuperAdmin->value => $allPermissions,
            RoleName::Admin->value => $allPermissions,
            RoleName::Owner->value => $companyUserPermissions,
            RoleName::Manager->value => [
                'dashboard.view',
                'users.view',
                'users.update',
                'company.users.view',
            ],
            RoleName::Engineer->value => [
                'dashboard.view',
                'users.view',
                'company.users.view',
            ],
            RoleName::Employee->value => [
                'dashboard.view',
                'company.users.view',
            ],
            RoleName::Guest->value => [
                'dashboard.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
