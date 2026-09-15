<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\RoleManagementService;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Presentation\Http\Requests\StoreRoleRequest;
use App\Modules\Identity\Presentation\Http\Requests\UpdateRoleRequest;
use App\Modules\Identity\Presentation\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(
            Role::query()->with('permissions')->orderBy('name')->paginate()
        );
    }

    public function store(StoreRoleRequest $request, RoleManagementService $roles): JsonResponse
    {
        $role = $roles->create($request->validated());

        return (new RoleResource($role))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role, RoleManagementService $roles): RoleResource
    {
        return new RoleResource($roles->update($role, $request->validated()));
    }

    public function destroy(Role $role, RoleManagementService $roles): Response
    {
        $roles->delete($role);

        return response()->noContent();
    }
}
