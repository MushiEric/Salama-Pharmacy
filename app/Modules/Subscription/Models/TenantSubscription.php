<?php

namespace App\Modules\Subscription\Models;

use App\Modules\Subscription\Domain\Enums\SubscriptionStatus;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Tenant;
use Database\Factories\TenantSubscriptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SubscriptionStatus $status
 */
class TenantSubscription extends Model
{
    /** @use HasFactory<TenantSubscriptionFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'package_id',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function isReadOnly(): bool
    {
        return $this->status->isReadOnly();
    }

    public function hasFeature(string $feature): bool
    {
        return $this->package->hasFeature($feature);
    }

    protected static function newFactory(): TenantSubscriptionFactory
    {
        return TenantSubscriptionFactory::new();
    }
}
