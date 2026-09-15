<?php

namespace App\Modules\Subscription\Application;

use App\Modules\Subscription\Domain\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Models\Tenant;

class TenantSubscriptionManagementService
{
    /**
     * @param  array{package_id?: string, status?: string, trial_ends_at?: string|null, current_period_ends_at?: string|null}  $payload
     */
    public function assign(Tenant $tenant, array $payload): TenantSubscription
    {
        $subscription = TenantSubscription::withoutGlobalScopes()
            ->firstOrNew(['tenant_id' => $tenant->id]);

        if (isset($payload['package_id'])) {
            $subscription->package_id = $payload['package_id'];
        }

        if (isset($payload['status'])) {
            $subscription->status = SubscriptionStatus::from($payload['status']);
        } elseif (! $subscription->exists) {
            $subscription->status = SubscriptionStatus::Trial;
        }

        if (array_key_exists('trial_ends_at', $payload)) {
            $subscription->trial_ends_at = $payload['trial_ends_at'];
        }

        if (array_key_exists('current_period_ends_at', $payload)) {
            $subscription->current_period_ends_at = $payload['current_period_ends_at'];
        }

        $subscription->tenant_id = $tenant->id;
        $subscription->save();

        return $subscription->load('package');
    }

    public function createInitialSubscription(Tenant $tenant, Package $package): TenantSubscription
    {
        return TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }
}
