<?php

namespace App\Enums;

use App\Models\User;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Owner = 'owner';
    case Manager = 'manager';
    case Engineer = 'engineer';
    case Employee = 'employee';
    case Guest = 'guest';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function privileged(): array
    {
        return [self::SuperAdmin->value, self::Admin->value];
    }

    public static function platform(): array
    {
        return [
            self::SuperAdmin->value,
            self::Admin->value,
            self::Manager->value,
            self::Engineer->value,
            self::Employee->value,
            self::Guest->value,
        ];
    }

    public static function company(): array
    {
        return [
            self::Owner->value,
            self::Manager->value,
            self::Engineer->value,
            self::Employee->value,
        ];
    }

    public static function ownerAssignable(): array
    {
        return [
            self::Manager->value,
            self::Engineer->value,
            self::Employee->value,
        ];
    }

    public static function adminAssignable(): array
    {
        return [
            self::Manager->value,
            self::Engineer->value,
            self::Employee->value,
            self::Guest->value,
        ];
    }

    public static function assignableByPlatformUser(User $user): array
    {
        if ($user->hasRole(self::SuperAdmin->value)) {
            return self::platform();
        }

        if ($user->hasRole(self::Admin->value)) {
            return self::adminAssignable();
        }

        return [];
    }
}
