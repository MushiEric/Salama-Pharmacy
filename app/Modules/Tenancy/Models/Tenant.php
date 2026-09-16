<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Subscription\Models\TenantSubscription;
use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use App\Modules\Tenancy\Domain\ValueObjects\TenantSettings;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property TenantStatus $status
 * @property array<string, mixed> $settings
 */
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'settings' => 'array',
        ];
    }

    /**
     * @return HasMany<Branch, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasOne<TenantSubscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === TenantStatus::Active;
    }

    public function settings(): TenantSettings
    {
        return TenantSettings::fromArray($this->getAttribute('settings') ?? []);
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
