<?php

use App\Modules\Identity\Domain\PermissionCatalog;
use App\Modules\Identity\Presentation\Http\Controllers\AuthController;
use App\Modules\Identity\Presentation\Http\Controllers\PermissionController;
use App\Modules\Identity\Presentation\Http\Controllers\RoleController;
use App\Modules\Identity\Presentation\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'tenant.context', 'branch.context'])->group(function (): void {
    Route::get('/user', [AuthController::class, 'user']);

    Route::middleware('tenant')->group(function (): void {
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('permission:'.PermissionCatalog::USER_VIEW);
        Route::post('/users', [UserController::class, 'store'])
            ->middleware('permission:'.PermissionCatalog::USER_CREATE);
        Route::get('/users/{user}', [UserController::class, 'show'])
            ->middleware('permission:'.PermissionCatalog::USER_VIEW);
        Route::patch('/users/{user}', [UserController::class, 'update'])
            ->middleware('permission:'.PermissionCatalog::USER_UPDATE);

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);
        Route::get('/roles/{role}', [RoleController::class, 'show'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);
        Route::patch('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:'.PermissionCatalog::ROLE_MANAGE);
    });
});
