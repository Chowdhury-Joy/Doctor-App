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
    Route::get('/manifest.json', function () {
        $tenant = tenant();
        return response()->json([
            'name' => $tenant->data['name'] ?? 'Doctor Booking',
            'short_name' => 'Booking',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $tenant->data['theme_color'] ?? '#0ea5e9',
            'icons' => [
                [
                    'src' => '/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ]);
    })->name('tenant.manifest');

    Route::get('/', [\App\Http\Controllers\TenantFrontendController::class, 'index'])->name('tenant.index');

    Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])
        ->middleware(['throttle:5,1', 'billing.active'])
        ->name('booking.store');
    Route::get('/bookings/{id}', [\App\Http\Controllers\BookingController::class, 'show'])->name('booking.show');
    Route::get('/queue/status/{sessionId}', [\App\Http\Controllers\QueueController::class, 'status'])->name('queue.status');
    
    // CSRF for webhooks is disabled in bootstrap/app.php
    Route::post('/payment/webhook/{gateway}', [\App\Http\Controllers\PaymentWebhookController::class, 'handle'])->name('payment.webhook');
});
