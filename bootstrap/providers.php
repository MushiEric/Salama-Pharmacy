<?php

use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Subscription\Providers\SubscriptionServiceProvider;
use App\Modules\Tenancy\Providers\TenancyServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    TenancyServiceProvider::class,
    IdentityServiceProvider::class,
    SubscriptionServiceProvider::class,
];
