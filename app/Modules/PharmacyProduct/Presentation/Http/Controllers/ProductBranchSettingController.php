<?php

namespace App\Modules\PharmacyProduct\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PharmacyProduct\Application\ProductManagementService;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Presentation\Http\Requests\UpsertProductBranchSettingRequest;
use App\Modules\PharmacyProduct\Presentation\Http\Resources\ProductBranchSettingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductBranchSettingController extends Controller
{
    public function index(PharmacyProduct $pharmacyProduct): AnonymousResourceCollection
    {
        return ProductBranchSettingResource::collection(
            $pharmacyProduct->branchSettings()->with('branch')->get()
        );
    }

    public function store(
        UpsertProductBranchSettingRequest $request,
        PharmacyProduct $pharmacyProduct,
        ProductManagementService $products,
    ): ProductBranchSettingResource {
        return new ProductBranchSettingResource(
            $products->upsertBranchSetting($pharmacyProduct, $request->validated())
        );
    }
}
