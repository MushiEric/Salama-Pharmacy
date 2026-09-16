<?php

namespace App\Modules\DrugCatalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DrugCatalog\Domain\Enums\CatalogStatus;
use App\Modules\DrugCatalog\Models\MasterDrug;
use App\Modules\DrugCatalog\Presentation\Http\Requests\StoreMasterDrugRequest;
use App\Modules\DrugCatalog\Presentation\Http\Requests\UpdateMasterDrugRequest;
use App\Modules\DrugCatalog\Presentation\Http\Resources\MasterDrugResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MasterDrugController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return MasterDrugResource::collection(
            MasterDrug::query()
                ->with('genericDrug')
                ->when(
                    $request->filled('search'),
                    fn ($query) => $query->where('brand_name', 'ilike', '%'.$request->string('search').'%')
                )
                ->orderBy('brand_name')
                ->paginate()
        );
    }

    public function store(StoreMasterDrugRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] ??= CatalogStatus::Active->value;

        $drug = MasterDrug::query()->create($data);

        return (new MasterDrugResource($drug->load('genericDrug')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MasterDrug $masterDrug): MasterDrugResource
    {
        return new MasterDrugResource($masterDrug->load('genericDrug'));
    }

    public function update(UpdateMasterDrugRequest $request, MasterDrug $masterDrug): MasterDrugResource
    {
        $masterDrug->fill($request->validated());
        $masterDrug->save();

        return new MasterDrugResource($masterDrug->load('genericDrug'));
    }
}
