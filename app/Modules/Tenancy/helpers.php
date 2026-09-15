<?php

use App\Modules\Tenancy\Application\TenantContext;

if (! function_exists('tenant_context')) {
    function tenant_context(): TenantContext
    {
        return app(TenantContext::class);
    }
}
