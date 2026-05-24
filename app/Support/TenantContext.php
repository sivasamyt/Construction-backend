<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Domain;

class TenantContext
{
    protected ?Company $company = null;

    protected ?Domain $domain = null;

    public function set(Company $company, Domain $domain): void
    {
        $this->company = $company;
        $this->domain = $domain;
    }

    public function clear(): void
    {
        $this->company = null;
        $this->domain = null;
    }

    public function isResolved(): bool
    {
        return $this->company !== null;
    }

    public function company(): ?Company
    {
        return $this->company;
    }

    public function domain(): ?Domain
    {
        return $this->domain;
    }

    public function companyId(): ?int
    {
        return $this->company?->id;
    }

    public function domainName(): ?string
    {
        return $this->domain?->domain;
    }
}
