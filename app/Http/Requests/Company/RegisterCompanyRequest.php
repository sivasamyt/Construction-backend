<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $domain = app(\App\Services\DomainSlugService::class)->generate($this->input('domain'));

            if ($domain !== '') {
                $this->merge(['domain' => $domain]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:domains,domain'],
            'contact_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex' => 'Domain may only contain lowercase letters, numbers, and hyphens.',
            'domain.unique' => 'This domain is already taken.',
        ];
    }
}
