<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PharmacyProduct\Application\ProductManagementService;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\PharmacyProduct\Presentation\Http\Requests\StoreProductUnitRequest;
use App\Modules\PharmacyProduct\Presentation\Http\Requests\UpdateProductUnitRequest;
use App\Modules\PharmacyProduct\Presentation\Http\Resources\ProductUnitResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductUnitController extends Controller
{
    public function store(
        StoreProductUnitRequest $request,
        PharmacyProduct $pharmacyProduct,
        ProductManagementService $products,
    ): JsonResponse {
        $unit = $products->addUnit($pharmacyProduct, $request->validated());

        return (new ProductUnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateProductUnitRequest $request,
        PharmacyProduct $pharmacyProduct,
        ProductUnit $unit,
        ProductManagementService $products,
    ): ProductUnitResource {
        if ($unit->pharmacy_product_id !== $pharmacyProduct->id) {
            throw new NotFoundHttpException;
        }

        return new ProductUnitResource($products->updateUnit($unit, $request->validated()));
    }
}
