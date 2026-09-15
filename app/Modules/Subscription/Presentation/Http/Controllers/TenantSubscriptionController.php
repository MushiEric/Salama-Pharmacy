<?php

namespace App\Modules\Subscription\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Application\TenantSubscriptionManagementService;
use App\Modules\Subscription\Presentation\Http\Requests\UpdateTenantSubscriptionRequest;
use App\Modules\Subscription\Presentation\Http\Resources\TenantSubscriptionResource;
use App\Modules\Tenancy\Models\Tenant;

class TenantSubscriptionController extends Controller
{
    public function show(Tenant $tenant): TenantSubscriptionResource
    {
        $subscription = $tenant->subscription()->withoutGlobalScopes()->with('package')->firstOrFail();

        return new TenantSubscriptionResource($subscription);
    }

    public function update(
        UpdateTenantSubscriptionRequest $request,
        Tenant $tenant,
        TenantSubscriptionManagementService $subscriptions,
    ): TenantSubscriptionResource {
        return new TenantSubscriptionResource(
            $subscriptions->assign($tenant, $request->validated())
        );
    }
}
