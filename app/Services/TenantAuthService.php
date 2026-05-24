<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\Company;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantAuthService
{
    public function __construct(
        private readonly TenantContext $tenant,
    ) {}

    public function login(string $email, string $password, string $deviceName = 'api-token'): array
    {
        $company = $this->tenant->company();

        if (! $company) {
            throw ValidationException::withMessages([
                'domain' => ['Invalid tenant context.'],
            ]);
        }

        $user = User::where('email', $email)
            ->where('company_id', $company->id)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user->load('roles', 'permissions', 'company.domain'),
            'token' => $token,
            'token_type' => 'Bearer',
            'company' => $company->load('domain'),
            'domain' => $this->tenant->domainName(),
        ];
    }

    public function registerOwner(Company $company, array $data): array
    {
        if ($company->hasOwner()) {
            throw ValidationException::withMessages([
                'email' => ['This company already has an owner.'],
            ]);
        }

        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered.'],
            ]);
        }

        $user = User::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole(RoleName::Owner->value);

        $deviceName = $data['device_name'] ?? 'api-token';
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user->load('roles', 'permissions', 'company.domain'),
            'token' => $token,
            'token_type' => 'Bearer',
            'company' => $company->load('domain'),
            'domain' => $company->domain?->domain,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
