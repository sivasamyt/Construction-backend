<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permissions.update') ?? false;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permissionId)],
        ];
    }
}
