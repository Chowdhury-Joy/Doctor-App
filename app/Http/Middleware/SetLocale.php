<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        $defaultLocale = $tenant ? ($tenant->default_locale ?? 'en') : 'en';

        $locale = session('locale', $defaultLocale);
        App::setLocale($locale);

        return $next($request);
    }
}
