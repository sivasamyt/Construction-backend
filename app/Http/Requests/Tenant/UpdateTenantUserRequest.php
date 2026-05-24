<?php

namespace App\Http\Requests\Tenant;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('company.users.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'password' => ['sometimes', 'string', 'min:6', 'confirmed'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', 'in:'.implode(',', RoleName::ownerAssignable())],
        ];
    }
}
