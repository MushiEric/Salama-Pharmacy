<?php

namespace App\Modules\Tenancy\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\TenantProvisioningService;
use App\Modules\Tenancy\Domain\Enums\TenantStatus;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Presentation\Http\Requests\StoreTenantRequest;
use App\Modules\Tenancy\Presentation\Http\Requests\UpdateTenantRequest;
use App\Modules\Tenancy\Presentation\Http\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TenantResource::collection(
            Tenant::query()->orderBy('name')->paginate()
        );
    }

    public function store(StoreTenantRequest $request, TenantProvisioningService $provisioning): JsonResponse
    {
        $result = $provisioning->provision($request->validated());

        return (new TenantResource($result['tenant']))
            ->additional([
                'branch' => [
                    'id' => $result['branch']->id,
                    'name' => $result['branch']->name,
                ],
                'admin' => [
                    'id' => $result['admin']->id,
                    'email' => $result['admin']->email,
                ],
                'subscription' => [
                    'id' => $result['subscription']->id,
                    'status' => $result['subscription']->status,
                    'package_id' => $result['subscription']->package_id,
                ],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Tenant $tenant): TenantResource
    {
        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): TenantResource
    {
        $tenant->fill($request->validated());
        $tenant->save();

        return new TenantResource($tenant);
    }

    public function suspend(Tenant $tenant): TenantResource
    {
        $tenant->status = TenantStatus::Suspended;
        $tenant->save();

        return new TenantResource($tenant);
    }
}
