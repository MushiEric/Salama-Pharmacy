<?php

namespace App\Modules\Tenancy\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Presentation\Http\Requests\UpdateTenantSettingsRequest;
use App\Modules\Tenancy\Presentation\Http\Resources\TenantSettingsResource;

class TenantSettingsController extends Controller
{
    public function show(TenantContext $tenantContext): TenantSettingsResource
    {
        return new TenantSettingsResource($tenantContext->tenant()->settings());
    }

    public function update(UpdateTenantSettingsRequest $request, TenantContext $tenantContext): TenantSettingsResource
    {
        $tenant = $tenantContext->tenant();
        $updated = $tenant->settings()->withOverrides($request->validated());

        $tenant->settings = $updated->toArray();
        $tenant->save();

        return new TenantSettingsResource($updated);
    }
}
