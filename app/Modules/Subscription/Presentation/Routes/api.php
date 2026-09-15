<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Subscription\Presentation\Http\Controllers\PackageController;
use App\Modules\Subscription\Presentation\Http\Controllers\SubscriptionController;
use App\Modules\Subscription\Presentation\Http\Controllers\TenantSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context'])->group(function (): void {
    Route::middleware('tenant')->group(function (): void {
        Route::get('/subscription', [SubscriptionController::class, 'show'])
            ->middleware('permission:'.PermissionCatalog::SUBSCRIPTION_VIEW);
    });

    Route::middleware('platform')->prefix('platform')->group(function (): void {
        Route::get('/packages', [PackageController::class, 'index']);
        Route::post('/packages', [PackageController::class, 'store']);
        Route::get('/packages/{package}', [PackageController::class, 'show']);
        Route::patch('/packages/{package}', [PackageController::class, 'update']);

        Route::get('/tenants/{tenant}/subscription', [TenantSubscriptionController::class, 'show']);
        Route::patch('/tenants/{tenant}/subscription', [TenantSubscriptionController::class, 'update']);
    });
});
