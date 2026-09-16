<?php

namespace App\Modules\Procurement\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Procurement\Application\StockReceivingService;
use App\Modules\Procurement\Models\StockReceipt;
use App\Modules\Procurement\Presentation\Http\Requests\StoreStockReceiptRequest;
use App\Modules\Procurement\Presentation\Http\Resources\StockReceiptResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockReceiptController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return StockReceiptResource::collection(
            StockReceipt::query()->with('items')->latest('received_at')->paginate()
        );
    }

    public function store(StoreStockReceiptRequest $request, StockReceivingService $receiving): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $receipt = $receiving->receive($request->validated(), $actor);

        return (new StockReceiptResource($receipt))
            ->response()
            ->setStatusCode(201);
    }

    public function show(StockReceipt $stockReceipt): StockReceiptResource
    {
        return new StockReceiptResource($stockReceipt->load('items'));
    }
}
