<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Inventory\Presentation\Http\Controllers\InventoryAdjustmentController;
use App\Modules\Inventory\Presentation\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context', 'tenant'])->group(function (): void {
    Route::get('/inventory', [InventoryController::class, 'balance'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::get('/inventory/batches', [InventoryController::class, 'batches'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::get('/inventory/expiring', [InventoryController::class, 'expiring'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::post('/inventory/adjustments', [InventoryAdjustmentController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_ADJUST);
});
