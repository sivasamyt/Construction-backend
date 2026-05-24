<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Domain;
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
}
