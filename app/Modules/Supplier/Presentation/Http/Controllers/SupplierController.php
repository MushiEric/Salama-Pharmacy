<?php

namespace App\Modules\Supplier\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Supplier\Domain\Enums\SupplierStatus;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Presentation\Http\Requests\StoreSupplierRequest;
use App\Modules\Supplier\Presentation\Http\Requests\UpdateSupplierRequest;
use App\Modules\Supplier\Presentation\Http\Resources\SupplierResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return SupplierResource::collection(
            Supplier::query()->orderBy('name')->paginate()
        );
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] ??= SupplierStatus::Active->value;

        $supplier = Supplier::query()->create($data);

        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): SupplierResource
    {
        $supplier->fill($request->validated());
        $supplier->save();

        return new SupplierResource($supplier);
    }
}
