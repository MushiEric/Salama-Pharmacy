<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PharmacyProduct\Application\ProductManagementService;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Presentation\Http\Requests\StorePharmacyProductRequest;
use App\Modules\PharmacyProduct\Presentation\Http\Requests\UpdatePharmacyProductRequest;
use App\Modules\PharmacyProduct\Presentation\Http\Resources\PharmacyProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PharmacyProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PharmacyProductResource::collection(
            PharmacyProduct::query()->with(['units', 'branchSettings'])->orderBy('local_name')->paginate()
        );
    }

    public function store(StorePharmacyProductRequest $request, ProductManagementService $products): JsonResponse
    {
        $product = $products->createProduct($request->validated());

        return (new PharmacyProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PharmacyProduct $pharmacyProduct): PharmacyProductResource
    {
        return new PharmacyProductResource($pharmacyProduct->load(['units', 'branchSettings']));
    }

    public function update(
        UpdatePharmacyProductRequest $request,
        PharmacyProduct $pharmacyProduct,
        ProductManagementService $products,
    ): PharmacyProductResource {
        return new PharmacyProductResource(
            $products->updateProduct($pharmacyProduct, $request->validated())
        );
    }
}
