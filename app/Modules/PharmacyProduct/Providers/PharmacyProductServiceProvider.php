<?php

namespace App\Modules\PharmacyProduct\Providers;

use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\Tenancy\Infrastructure\Persistence\TenantScopedBinding;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PharmacyProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('pharmacyProduct', fn (string $value): PharmacyProduct => TenantScopedBinding::resolve(PharmacyProduct::class, $value));
        Route::bind('unit', fn (string $value): ProductUnit => TenantScopedBinding::resolve(ProductUnit::class, $value));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/PharmacyProduct/Presentation/Routes/api.php'));
    }
}
