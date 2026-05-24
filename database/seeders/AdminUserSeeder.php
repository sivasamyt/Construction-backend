<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@cons.local'],
            [
                'name' => 'Sivasamy',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->syncRoles([RoleName::SuperAdmin->value]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@rbac.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([RoleName::Admin->value]);
    }
}
