<?php

namespace App\Http\Requests\User;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.assign-roles') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(RoleName::values())],
        ];
    }
}
