<?php

namespace App\Modules\Subscription\Providers;

use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SubscriptionEntitlementService::class);
    }

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('app/Modules/Subscription/Presentation/Routes/api.php'));
    }
}
