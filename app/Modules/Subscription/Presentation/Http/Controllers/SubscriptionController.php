<?php

namespace App\Modules\Subscription\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use App\Modules\Subscription\Presentation\Http\Resources\TenantSubscriptionResource;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function show(SubscriptionEntitlementService $entitlements): TenantSubscriptionResource|JsonResponse
    {
        $subscription = $entitlements->currentSubscription();

        if ($subscription === null) {
            return response()->json(['data' => null], 404);
        }

        return new TenantSubscriptionResource($subscription);
    }
}
