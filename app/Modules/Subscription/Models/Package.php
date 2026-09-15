<?php

namespace App\Modules\Subscription\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'max_branches',
        'max_users',
        'max_devices',
        'max_stock_items',
        'max_transactions_per_period',
        'features',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_branches' => 'integer',
            'max_users' => 'integer',
            'max_devices' => 'integer',
            'max_stock_items' => 'integer',
            'max_transactions_per_period' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TenantSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    protected static function newFactory(): PackageFactory
    {
        return PackageFactory::new();
    }
}
