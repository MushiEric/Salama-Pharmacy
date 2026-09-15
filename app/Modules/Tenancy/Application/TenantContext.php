<?php

namespace App\Modules\Tenancy\Application;

use App\Modules\Tenancy\Domain\Exceptions\TenantContextNotResolvedException;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Models\Tenant;
use Closure;

final class TenantContext
{
    private ?Tenant $tenant = null;

    private ?Branch $branch = null;

    private bool $platformContext = false;

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->platformContext = false;
    }

    public function setBranch(?Branch $branch): void
    {
        $this->branch = $branch;
    }

    public function enterPlatformContext(): void
    {
        $this->platformContext = true;
        $this->tenant = null;
        $this->branch = null;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->branch = null;
        $this->platformContext = false;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function isPlatformContext(): bool
    {
        return $this->platformContext;
    }

    public function tenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new TenantContextNotResolvedException;
        }

        return $this->tenant;
    }

    public function tenantId(): string
    {
        return $this->tenant()->id;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function branchId(): ?string
    {
        return $this->branch?->id;
    }

    public function hasBranch(): bool
    {
        return $this->branch !== null;
    }

    /**
     * Explicit elevated path for authorized platform operations.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function withoutTenantScopeForAuthorizedPlatformAction(Closure $callback): mixed
    {
        $previous = $this->snapshot();
        $this->enterPlatformContext();

        try {
            return $callback();
        } finally {
            $this->restore($previous);
        }
    }

    /**
     * @return array{tenant: ?Tenant, branch: ?Branch, platform: bool}
     */
    private function snapshot(): array
    {
        return [
            'tenant' => $this->tenant,
            'branch' => $this->branch,
            'platform' => $this->platformContext,
        ];
    }

    /**
     * @param  array{tenant: ?Tenant, branch: ?Branch, platform: bool}  $snapshot
     */
    private function restore(array $snapshot): void
    {
        $this->tenant = $snapshot['tenant'];
        $this->branch = $snapshot['branch'];
        $this->platformContext = $snapshot['platform'];
    }
}
