<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceBillingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant('billing_status') === 'read_only') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This clinic account is currently read-only. Please contact support to enable bookings.'], 403);
            }
            
            return redirect()->back()->with('error', 'This clinic account is currently read-only. Please contact support to enable bookings.');
        }

        return $next($request);
    }
}
