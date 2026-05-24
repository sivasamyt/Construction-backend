<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $email, string $password, string $deviceName = 'api-token'): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isPlatformUser()) {
            throw ValidationException::withMessages([
                'email' => ['Please sign in from your company portal.'],
            ]);
        }

        if (! $user->hasAnyRole(RoleName::privileged())) {
            throw ValidationException::withMessages([
                'email' => ['Platform login is only available for administrators.'],
            ]);
        }

        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
