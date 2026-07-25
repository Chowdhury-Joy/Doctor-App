<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAccessFromTenantDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the current host is not in the central domains list, we should block it.
        $centralDomains = config('tenancy.central_domains', []);
        
        if (!in_array($request->getHost(), $centralDomains)) {
            abort(404);
        }

        return $next($request);
    }
}
