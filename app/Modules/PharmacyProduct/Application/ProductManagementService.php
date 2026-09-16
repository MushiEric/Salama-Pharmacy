<?php

namespace App\Modules\PharmacyProduct\Application;

use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductBranchSetting;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use App\Modules\Tenancy\Application\BranchAccessService;
use App\Modules\Tenancy\Application\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductManagementService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly BranchAccessService $branchAccess,
        private readonly SubscriptionEntitlementService $entitlements,
    ) {}

    /**
     * @param  array{master_drug_id?: string|null, local_name: string, prescription_required?: bool, status?: string}  $payload
     */
    public function createProduct(array $payload): PharmacyProduct
    {
        $this->entitlements->assertStockLimit();

        return PharmacyProduct::query()->create([
            'tenant_id' => $this->tenantContext->tenantId(),
            'master_drug_id' => $payload['master_drug_id'] ?? null,
            'local_name' => $payload['local_name'],
            'prescription_required' => $payload['prescription_required'] ?? false,
            'status' => $payload['status'] ?? ProductStatus::Active->value,
        ]);
    }

    /**
     * @param  array{master_drug_id?: string|null, local_name?: string, prescription_required?: bool, status?: string}  $payload
     */
    public function updateProduct(PharmacyProduct $product, array $payload): PharmacyProduct
    {
        $product->fill($payload);
        $product->save();

        return $product;
    }

    /**
     * @param  array{name: string, symbol?: string|null, multiplier_to_base: float, is_base?: bool, status?: string}  $payload
     */
    public function addUnit(PharmacyProduct $product, array $payload): ProductUnit
    {
        return DB::transaction(function () use ($product, $payload): ProductUnit {
            $isBase = (bool) ($payload['is_base'] ?? false);

            if ($isBase) {
                $product->units()->where('is_base', true)->update(['is_base' => false]);
            }

            return $product->units()->create([
                'tenant_id' => $product->tenant_id,
                'name' => $payload['name'],
                'symbol' => $payload['symbol'] ?? null,
                'multiplier_to_base' => $payload['multiplier_to_base'],
                'is_base' => $isBase,
                'status' => $payload['status'] ?? ProductStatus::Active->value,
            ]);
        });
    }

    /**
     * @param  array{name?: string, symbol?: string|null, multiplier_to_base?: float, is_base?: bool, status?: string}  $payload
     */
    public function updateUnit(ProductUnit $unit, array $payload): ProductUnit
    {
        return DB::transaction(function () use ($unit, $payload): ProductUnit {
            if (($payload['is_base'] ?? false) === true && ! $unit->is_base) {
                ProductUnit::query()
                    ->where('pharmacy_product_id', $unit->pharmacy_product_id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }

            if (($payload['is_base'] ?? null) === false && $unit->is_base) {
                throw ValidationException::withMessages([
                    'is_base' => 'A product must always have exactly one base unit. Promote another unit to base first.',
                ]);
            }

            $unit->fill($payload);
            $unit->save();

            return $unit;
        });
    }

    /**
     * @param  array{branch_id: string, selling_price: float, reorder_level_base?: float, is_active?: bool}  $payload
     */
    public function upsertBranchSetting(PharmacyProduct $product, array $payload): ProductBranchSetting
    {
        $branch = $this->branchAccess->assertBranchInCurrentTenant($payload['branch_id']);

        return ProductBranchSetting::query()->updateOrCreate(
            [
                'tenant_id' => $product->tenant_id,
                'branch_id' => $branch->id,
                'pharmacy_product_id' => $product->id,
            ],
            [
                'selling_price' => $payload['selling_price'],
                'reorder_level_base' => $payload['reorder_level_base'] ?? 0,
                'is_active' => $payload['is_active'] ?? true,
            ],
        );
    }
}
