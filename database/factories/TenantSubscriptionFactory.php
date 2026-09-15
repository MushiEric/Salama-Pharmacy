<?php

namespace Database\Factories;

use App\Modules\Subscription\Domain\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSubscription>
 */
class TenantSubscriptionFactory extends Factory
{
    protected $model = TenantSubscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'package_id' => Package::factory(),
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => now()->addDays(14),
            'current_period_ends_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'current_period_ends_at' => now()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Expired,
            'current_period_ends_at' => now()->subDay(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Suspended,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
