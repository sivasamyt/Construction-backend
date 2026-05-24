<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->isPlatformUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Platform users cannot access tenant resources.',
            ], 403);
        }

        if (! $this->tenant->isResolved() || ! $user->belongsToCompany($this->tenant->companyId())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this company.',
            ], 403);
        }

        return $next($request);
    }
}
