<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Application\Services\PermissionResolver;
use App\Modules\Identity\Domain\Enums\SystemRole;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property UserStatus $status
 * @property string|null $tenant_id
 * @property string|null $branch_id
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
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
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isPlatformSuperadmin(): bool
    {
        return $this->tenant_id === null
            && $this->hasSystemRole(SystemRole::PlatformSuperadmin);
    }

    public function isPharmacyAdmin(): bool
    {
        return $this->tenant_id !== null
            && $this->hasSystemRole(SystemRole::PharmacyAdmin);
    }

    public function hasPermission(string $code): bool
    {
        return app(PermissionResolver::class)->userHas($this, $code);
    }

    public function hasSystemRole(SystemRole $role): bool
    {
        return $this->roles()
            ->withoutGlobalScopes()
            ->where('slug', $role->value)
            ->where('is_system', true)
            ->when(
                $this->tenant_id === null,
                fn ($query) => $query->whereNull('roles.tenant_id'),
                fn ($query) => $query->where('roles.tenant_id', $this->tenant_id),
            )
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function permissionCodes(): array
    {
        return app(PermissionResolver::class)->codesFor($this);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
