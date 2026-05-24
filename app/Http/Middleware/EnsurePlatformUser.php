<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isPlatformUser()) {
            return response()->json([
                'success' => false,
                'message' => 'This action is restricted to platform users.',
            ], 403);
        }

        return $next($request);
    }
}
