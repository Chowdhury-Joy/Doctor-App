<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', [\App\Http\Controllers\TenantFrontendController::class, 'index'])->name('tenant.index');

    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
    Route::get('/bookings/{id}', [\App\Http\Controllers\BookingController::class, 'show'])->name('booking.show');
    Route::get('/queue/status/{sessionId}', [\App\Http\Controllers\QueueController::class, 'status'])->name('queue.status');
    
    // Disable CSRF for webhooks. In Laravel 12 this can be done in bootstrap/app.php, but for scaffolding we just map the route.
    Route::post('/payment/webhook/{gateway}', [\App\Http\Controllers\PaymentWebhookController::class, 'handle'])->name('payment.webhook')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});
