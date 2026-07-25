<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
| The root path is claimed by the tenant site (routes/tenant.php). Registering an
| unconstrained "/" here would shadow it for every tenant, so the central landing
| route is bound explicitly to each central domain and tenant subdomains fall
| through to the tenant route as before. Without this, hitting the central domain
| root raises TenantCouldNotBeIdentifiedOnDomainException and returns a 500.
*/
foreach ((array) config('tenancy.central_domains') as $centralDomain) {
    Route::domain($centralDomain)->group(function () {
        Route::get('/', fn () => redirect()->route('filament.super-admin.pages.dashboard'));
    });
}

Route::middleware([\App\Http\Middleware\PreventAccessFromTenantDomains::class])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});
