<?php

use App\Modules\DrugCatalog\Presentation\Http\Controllers\GenericDrugController;
use App\Modules\DrugCatalog\Presentation\Http\Controllers\MasterDrugController;
use App\Modules\Identity\Domain\PermissionCatalog;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context'])->group(function (): void {
    Route::middleware('tenant')->group(function (): void {
        Route::get('/master-drugs', [MasterDrugController::class, 'index'])
            ->middleware('permission:'.PermissionCatalog::DRUG_VIEW);
        Route::get('/master-drugs/{masterDrug}', [MasterDrugController::class, 'show'])
            ->middleware('permission:'.PermissionCatalog::DRUG_VIEW);
    });

    Route::middleware('platform')->prefix('platform')->group(function (): void {
        Route::get('/generic-drugs', [GenericDrugController::class, 'index']);
        Route::post('/generic-drugs', [GenericDrugController::class, 'store']);
        Route::get('/generic-drugs/{genericDrug}', [GenericDrugController::class, 'show']);
        Route::patch('/generic-drugs/{genericDrug}', [GenericDrugController::class, 'update']);

        Route::post('/master-drugs', [MasterDrugController::class, 'store']);
        Route::patch('/master-drugs/{masterDrug}', [MasterDrugController::class, 'update']);
    });
});
