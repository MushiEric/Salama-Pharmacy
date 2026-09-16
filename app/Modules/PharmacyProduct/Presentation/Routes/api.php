<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\PharmacyProduct\Presentation\Http\Controllers\PharmacyProductController;
use App\Modules\PharmacyProduct\Presentation\Http\Controllers\ProductBranchSettingController;
use App\Modules\PharmacyProduct\Presentation\Http\Controllers\ProductUnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context', 'tenant'])->group(function (): void {
    Route::get('/products', [PharmacyProductController::class, 'index'])
        ->middleware('permission:'.PermissionCatalog::DRUG_VIEW);
    Route::post('/products', [PharmacyProductController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::PRODUCT_CREATE);
    Route::get('/products/{pharmacyProduct}', [PharmacyProductController::class, 'show'])
        ->middleware('permission:'.PermissionCatalog::DRUG_VIEW);
    Route::patch('/products/{pharmacyProduct}', [PharmacyProductController::class, 'update'])
        ->middleware('permission:'.PermissionCatalog::PRODUCT_UPDATE);

    Route::post('/products/{pharmacyProduct}/units', [ProductUnitController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::PRODUCT_UPDATE);
    Route::patch('/products/{pharmacyProduct}/units/{unit}', [ProductUnitController::class, 'update'])
        ->middleware('permission:'.PermissionCatalog::PRODUCT_UPDATE);

    Route::get('/products/{pharmacyProduct}/branch-settings', [ProductBranchSettingController::class, 'index'])
        ->middleware('permission:'.PermissionCatalog::DRUG_VIEW);
    Route::post('/products/{pharmacyProduct}/branch-settings', [ProductBranchSettingController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::PRODUCT_PRICE_UPDATE);
});
