<?php

namespace App\Http\Requests\Tenant;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenantUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('company.users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'in:'.implode(',', RoleName::ownerAssignable())],
        ];
    }
}
