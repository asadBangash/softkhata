<?php

namespace App\Http\Middleware;

use App\Services\BusinessContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            app(BusinessContext::class)->resolveBusinessId($user);
        }

        return $next($request);
    }
}
