<?php

namespace App\Services;

use App\Enums\RoleName;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class TenantUserService
{
    public function __construct(
        private readonly TenantContext $tenant,
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->where('company_id', $this->tenant->companyId())
            ->with('roles', 'permissions');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): User
    {
        $this->assertAssignableRoles($data['roles'] ?? []);
        $this->assertNoOwnerRole($data['roles'] ?? []);

        $companyId = $this->tenant->companyId();

        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered.'],
            ]);
        }

        $user = User::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles', 'permissions');
    }

    public function update(User $user, array $data): User
    {
        $this->assertUserInTenant($user);

        if ($user->isOwner() && array_key_exists('roles', $data)) {
            throw ValidationException::withMessages([
                'roles' => ['The company owner role cannot be changed.'],
            ]);
        }

        if (array_key_exists('roles', $data)) {
            $this->assertAssignableRoles($data['roles']);
            $this->assertNoOwnerRole($data['roles']);
        }

        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles', 'permissions');
    }

    public function delete(User $user): void
    {
        $this->assertUserInTenant($user);

        if ($user->isOwner()) {
            throw ValidationException::withMessages([
                'user' => ['The company owner cannot be deleted.'],
            ]);
        }

        $user->tokens()->delete();
        $user->delete();
    }

    private function assertUserInTenant(User $user): void
    {
        if (! $user->belongsToCompany($this->tenant->companyId())) {
            throw ValidationException::withMessages([
                'user' => ['User does not belong to this company.'],
            ]);
        }
    }

    private function assertAssignableRoles(array $roles): void
    {
        $invalid = array_diff($roles, RoleName::ownerAssignable());

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'roles' => ['Owners can only assign manager, engineer, or employee roles.'],
            ]);
        }
    }

    private function assertNoOwnerRole(array $roles): void
    {
        if (in_array(RoleName::Owner->value, $roles, true)) {
            throw ValidationException::withMessages([
                'roles' => ['A company can have only one owner.'],
            ]);
        }

        if ($this->tenant->company()?->hasOwner() && in_array(RoleName::Owner->value, $roles, true)) {
            throw ValidationException::withMessages([
                'roles' => ['This company already has an owner.'],
            ]);
        }
    }
}
