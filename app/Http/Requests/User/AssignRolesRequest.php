<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRolesRequest extends FormRequest
{
    use ValidatesPlatformUserRoles;

    public function authorize(): bool
    {
        return $this->user()?->can('users.assign-roles') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($this->assignablePlatformRoles())],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.*.in' => 'You are not allowed to assign one or more of the selected roles.',
        ];
    }
}
