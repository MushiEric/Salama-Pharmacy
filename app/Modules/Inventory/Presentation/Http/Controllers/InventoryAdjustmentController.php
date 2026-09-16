<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Application\InventoryAdjustmentService;
use App\Modules\Inventory\Presentation\Http\Requests\StoreInventoryAdjustmentRequest;
use App\Modules\Inventory\Presentation\Http\Resources\InventoryAdjustmentResource;
use Illuminate\Http\JsonResponse;

class InventoryAdjustmentController extends Controller
{
    public function store(StoreInventoryAdjustmentRequest $request, InventoryAdjustmentService $adjustments): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $adjustment = $adjustments->adjust($request->validated(), $actor);

        return (new InventoryAdjustmentResource($adjustment))
            ->response()
            ->setStatusCode(201);
    }
}
