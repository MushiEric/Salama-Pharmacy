<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Presentation\Http\Resources\InventoryBatchResource;
use App\Modules\Inventory\Presentation\Http\Resources\InventoryMovementResource;
use App\Modules\Tenancy\Application\BranchAccessService;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly BranchAccessService $branchAccess,
    ) {}

    public function batches(Request $request): AnonymousResourceCollection
    {
        $branchId = $this->resolveBranchFilter($request);

        return InventoryBatchResource::collection(
            InventoryBatch::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->when(
                    $request->filled('pharmacy_product_id'),
                    fn ($query) => $query->where('pharmacy_product_id', $request->string('pharmacy_product_id'))
                )
                ->when(
                    $request->filled('status'),
                    fn ($query) => $query->where('status', $request->string('status'))
                )
                ->orderBy('expires_at')
                ->paginate()
        );
    }

    public function balance(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->tenantId();
        $branchId = $this->resolveBranchFilter($request);

        $rows = DB::table('inventory_batches')
            ->select('branch_id', 'pharmacy_product_id')
            ->selectRaw('SUM(available_quantity_base) as available_quantity_base')
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when(
                $request->filled('pharmacy_product_id'),
                fn ($query) => $query->where('pharmacy_product_id', $request->string('pharmacy_product_id'))
            )
            ->groupBy('branch_id', 'pharmacy_product_id')
            ->havingRaw('SUM(available_quantity_base) > 0')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $tenantId = $this->tenantContext->tenantId();
        $branchId = $this->resolveBranchFilter($request);

        $balances = DB::table('inventory_batches')
            ->select('branch_id', 'pharmacy_product_id')
            ->selectRaw('SUM(available_quantity_base) as available_quantity_base')
            ->where('tenant_id', $tenantId)
            ->groupBy('branch_id', 'pharmacy_product_id');

        $rows = DB::table('product_branch_settings')
            ->leftJoinSub($balances, 'balances', function ($join): void {
                $join->on('product_branch_settings.branch_id', '=', 'balances.branch_id')
                    ->on('product_branch_settings.pharmacy_product_id', '=', 'balances.pharmacy_product_id');
            })
            ->where('product_branch_settings.tenant_id', $tenantId)
            ->where('product_branch_settings.is_active', true)
            ->when($branchId, fn ($query) => $query->where('product_branch_settings.branch_id', $branchId))
            ->whereRaw('COALESCE(balances.available_quantity_base, 0) <= product_branch_settings.reorder_level_base')
            ->select(
                'product_branch_settings.id',
                'product_branch_settings.branch_id',
                'product_branch_settings.pharmacy_product_id',
                'product_branch_settings.reorder_level_base',
            )
            ->selectRaw('COALESCE(balances.available_quantity_base, 0) as available_quantity_base')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function expiring(Request $request): AnonymousResourceCollection
    {
        $branchId = $this->resolveBranchFilter($request);
        $settings = $this->tenantContext->tenant()->settings();
        $threshold = now()->addDays($settings->expiryYellowDays);

        return InventoryBatchResource::collection(
            InventoryBatch::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->where('available_quantity_base', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $threshold)
                ->orderBy('expires_at')
                ->paginate()
        );
    }

    public function movements(Request $request): AnonymousResourceCollection
    {
        $branchId = $this->resolveBranchFilter($request);

        return InventoryMovementResource::collection(
            InventoryMovement::query()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->when(
                    $request->filled('pharmacy_product_id'),
                    fn ($query) => $query->where('pharmacy_product_id', $request->string('pharmacy_product_id'))
                )
                ->latest('occurred_at')
                ->paginate()
        );
    }

    private function resolveBranchFilter(Request $request): ?string
    {
        if ($request->filled('branch_id')) {
            return $this->branchAccess->assertBranchInCurrentTenant($request->string('branch_id')->toString())->id;
        }

        return $this->tenantContext->branch()?->id;
    }
}
