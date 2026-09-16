<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use App\Modules\DrugCatalog\Models\GenericDrug;
use App\Modules\DrugCatalog\Presentation\Http\Requests\StoreGenericDrugRequest;
use App\Modules\DrugCatalog\Presentation\Http\Requests\UpdateGenericDrugRequest;
use App\Modules\DrugCatalog\Presentation\Http\Resources\GenericDrugResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenericDrugController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GenericDrugResource::collection(
            GenericDrug::query()->orderBy('name')->paginate()
        );
    }

    public function store(StoreGenericDrugRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] ??= CatalogStatus::Active->value;

        $drug = GenericDrug::query()->create($data);

        return (new GenericDrugResource($drug))
            ->response()
            ->setStatusCode(201);
    }

    public function show(GenericDrug $genericDrug): GenericDrugResource
    {
        return new GenericDrugResource($genericDrug);
    }

    public function update(UpdateGenericDrugRequest $request, GenericDrug $genericDrug): GenericDrugResource
    {
        $genericDrug->fill($request->validated());
        $genericDrug->save();

        return new GenericDrugResource($genericDrug);
    }
}
