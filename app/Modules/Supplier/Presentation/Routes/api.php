<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Supplier\Presentation\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context', 'tenant'])->group(function (): void {
    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->middleware('permission:'.PermissionCatalog::SUPPLIER_VIEW);
    Route::post('/suppliers', [SupplierController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::SUPPLIER_MANAGE);
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
        ->middleware('permission:'.PermissionCatalog::SUPPLIER_VIEW);
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware('permission:'.PermissionCatalog::SUPPLIER_MANAGE);
});
