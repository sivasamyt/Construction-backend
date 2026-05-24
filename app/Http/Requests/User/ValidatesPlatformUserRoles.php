<?php

namespace App\Http\Requests\User;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

trait ValidatesPlatformUserRoles
{
    protected function assignablePlatformRoles(): array
    {
        $user = $this->user();

        if (! $user) {
            return [];
        }

        return RoleName::assignableByPlatformUser($user);
    }

    protected function platformRoleRules(): array
    {
        return [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($this->assignablePlatformRoles())],
        ];
    }
}
