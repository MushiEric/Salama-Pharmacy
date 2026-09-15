<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Tenancy\Presentation\Http\Controllers\BranchController;
use App\Modules\Tenancy\Presentation\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context'])->group(function (): void {
    Route::middleware('platform')->prefix('platform')->group(function (): void {
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
        Route::get('/tenants/{tenant}', [TenantController::class, 'show']);
        Route::patch('/tenants/{tenant}', [TenantController::class, 'update']);
        Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend']);
    });

    Route::middleware('tenant')->group(function (): void {
        Route::get('/branches', [BranchController::class, 'index'])
            ->middleware('permission:'.PermissionCatalog::BRANCH_VIEW);
        Route::post('/branches', [BranchController::class, 'store'])
            ->middleware('permission:'.PermissionCatalog::BRANCH_MANAGE);
        Route::get('/branches/{branch}', [BranchController::class, 'show'])
            ->middleware('permission:'.PermissionCatalog::BRANCH_VIEW);
        Route::patch('/branches/{branch}', [BranchController::class, 'update'])
            ->middleware('permission:'.PermissionCatalog::BRANCH_MANAGE);
    });
});
