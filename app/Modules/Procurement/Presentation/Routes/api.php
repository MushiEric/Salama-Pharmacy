<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Procurement\Presentation\Http\Controllers\StockReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context', 'tenant'])->group(function (): void {
    Route::get('/stock-receipts', [StockReceiptController::class, 'index'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
    Route::post('/stock-receipts', [StockReceiptController::class, 'store'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_RECEIVE);
    Route::get('/stock-receipts/{stockReceipt}', [StockReceiptController::class, 'show'])
        ->middleware('permission:'.PermissionCatalog::INVENTORY_VIEW);
});
