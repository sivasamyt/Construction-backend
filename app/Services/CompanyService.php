<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Domain;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyService
{
    public function __construct(
        private readonly DomainSlugService $domainSlugService,
    ) {}

    public function previewDomain(string $domainInput): array
    {
        $slug = $this->domainSlugService->generate($domainInput);

        return [
            'domain' => $slug,
            'available' => $slug !== '' && $this->domainSlugService->isAvailable($slug),
        ];
    }

    public function register(array $data): array
    {
        $domainName = $data['domain'];

        if (! $this->domainSlugService->isAvailable($domainName)) {
            throw ValidationException::withMessages([
                'domain' => ['This domain is already taken.'],
            ]);
        }

        return DB::transaction(function () use ($data, $domainName) {
            $company = Company::create([
                'name' => $data['name'],
                'contact_number' => $data['contact_number'],
                'email' => $data['email'],
                'address' => $data['address'],
            ]);

            $domain = Domain::create([
                'company_id' => $company->id,
                'domain' => $domainName,
            ]);

            return [
                'company' => $company->load('domain'),
                'domain' => $domain,
                'url' => $this->domainSlugService->buildUrl($domainName),
            ];
        });
    }

    public function resolveByDomain(string $domainName): ?Company
    {
        $domain = Domain::with('company.domain')
            ->where('domain', $domainName)
            ->first();

        return $domain?->company;
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Company::query()
            ->with('domain')
            ->with(['users' => fn ($q) => $q->role('owner')]);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('domain', fn ($d) => $d->where('domain', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->latest()->paginate($perPage);
    }

    public function find(Company $company): Company
    {
        return $company->load([
            'domain',
            'users' => fn ($q) => $q->role('owner'),
        ]);
    }
}
