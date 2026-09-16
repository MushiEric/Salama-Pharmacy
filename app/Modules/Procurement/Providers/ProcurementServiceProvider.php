<?php

namespace App\Modules\Procurement\Providers;

use App\Modules\Procurement\Models\StockReceipt;
use App\Modules\Tenancy\Infrastructure\Persistence\TenantScopedBinding;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProcurementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('stockReceipt', fn (string $value): StockReceipt => TenantScopedBinding::resolve(StockReceipt::class, $value));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Procurement/Presentation/Routes/api.php'));
    }
}
