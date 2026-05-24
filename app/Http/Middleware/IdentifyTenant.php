<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function handle(Request $request, Closure $next): Response
    {
        $domainName = $request->route('domain')
            ?? $request->header('X-Tenant-Domain');

        if (! $domainName) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant domain is required.',
            ], 400);
        }

        $domain = Domain::with('company')->where('domain', $domainName)->first();

        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found for this domain.',
            ], 404);
        }

        $this->tenant->set($domain->company, $domain);

        return $next($request);
    }
}
