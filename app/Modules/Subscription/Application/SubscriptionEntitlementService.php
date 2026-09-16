<?php

namespace App\Modules\Subscription\Application;

use App\Modules\Identity\Models\User;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Models\Branch;
use Closure;
use Illuminate\Validation\ValidationException;

class SubscriptionEntitlementService
{
    private bool $loaded = false;

    private ?TenantSubscription $subscription = null;

    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function currentSubscription(): ?TenantSubscription
    {
        if ($this->loaded) {
            return $this->subscription;
        }

        $this->loaded = true;

        if (! $this->tenantContext->hasTenant()) {
            return $this->subscription = null;
        }

        return $this->subscription = TenantSubscription::query()
            ->with('package')
            ->first();
    }

    public function isReadOnly(): bool
    {
        $subscription = $this->currentSubscription();

        return $subscription === null || $subscription->isReadOnly();
    }

    public function assertWriteAllowed(): void
    {
        if ($this->tenantContext->isPlatformContext()) {
            return;
        }

        if ($this->isReadOnly()) {
            abort(403, 'Subscription is not active. This tenant is in read-only mode.');
        }
    }

    public function canUse(string $feature): bool
    {
        $subscription = $this->currentSubscription();

        return $subscription !== null && $subscription->hasFeature($feature);
    }

    public function assertBranchLimit(): void
    {
        $this->assertCountLimit(
            countCallback: fn (): int => Branch::query()->count(),
            limitCallback: fn (Package $package): ?int => $package->max_branches,
            message: 'Branch limit reached for the current subscription package.',
        );
    }

    public function assertUserLimit(): void
    {
        $this->assertCountLimit(
            countCallback: fn (): int => User::query()->count(),
            limitCallback: fn (Package $package): ?int => $package->max_users,
            message: 'User limit reached for the current subscription package.',
        );
    }

    public function assertStockLimit(): void
    {
        $this->assertCountLimit(
            countCallback: fn (): int => PharmacyProduct::query()->count(),
            limitCallback: fn (Package $package): ?int => $package->max_stock_items,
            message: 'Product limit reached for the current subscription package.',
        );
    }

    /**
     * @param  Closure(): int  $countCallback
     * @param  Closure(Package): ?int  $limitCallback
     */
    private function assertCountLimit(Closure $countCallback, Closure $limitCallback, string $message): void
    {
        $subscription = $this->currentSubscription();

        if ($subscription === null) {
            return;
        }

        $limit = $limitCallback($subscription->package);

        if ($limit === null) {
            return;
        }

        if ($countCallback() >= $limit) {
            throw ValidationException::withMessages(['limit' => $message]);
        }
    }
}
