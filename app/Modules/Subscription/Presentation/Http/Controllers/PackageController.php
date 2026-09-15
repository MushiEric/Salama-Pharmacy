<?php

namespace App\Modules\Subscription\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Models\Package;
use App\Modules\Subscription\Presentation\Http\Requests\StorePackageRequest;
use App\Modules\Subscription\Presentation\Http\Requests\UpdatePackageRequest;
use App\Modules\Subscription\Presentation\Http\Resources\PackageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PackageResource::collection(
            Package::query()->orderBy('name')->get()
        );
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = Package::query()->create($request->validated());

        return (new PackageResource($package))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Package $package): PackageResource
    {
        return new PackageResource($package);
    }

    public function update(UpdatePackageRequest $request, Package $package): PackageResource
    {
        $package->fill($request->validated());
        $package->save();

        return new PackageResource($package);
    }
}
