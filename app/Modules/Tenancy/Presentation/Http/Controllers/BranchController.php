<?php

namespace App\Modules\Tenancy\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use App\Modules\Tenancy\Domain\Enums\BranchStatus;
use App\Modules\Tenancy\Models\Branch;
use App\Modules\Tenancy\Presentation\Http\Requests\StoreBranchRequest;
use App\Modules\Tenancy\Presentation\Http\Requests\UpdateBranchRequest;
use App\Modules\Tenancy\Presentation\Http\Resources\BranchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BranchResource::collection(
            Branch::query()->orderBy('name')->paginate()
        );
    }

    public function store(StoreBranchRequest $request, SubscriptionEntitlementService $entitlements): JsonResponse
    {
        $entitlements->assertBranchLimit();

        $data = $request->validated();
        $data['status'] ??= BranchStatus::Active->value;

        $branch = Branch::query()->create($data);

        return (new BranchResource($branch))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): BranchResource
    {
        $branch->fill($request->validated());
        $branch->save();

        return new BranchResource($branch);
    }
}
