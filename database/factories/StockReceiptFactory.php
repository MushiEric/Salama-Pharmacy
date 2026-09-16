<?php

namespace Database\Factories;

use App\Modules\Procurement\Domain\Enums\StockReceiptStatus;
use App\Modules\Procurement\Models\StockReceipt;
use App\Modules\Tenancy\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockReceipt>
 */
class StockReceiptFactory extends Factory
{
    protected $model = StockReceipt::class;

    public function configure(): static
    {
        return $this->afterMaking(function (StockReceipt $receipt): void {
            if ($receipt->tenant_id === null) {
                $receipt->tenant_id = Branch::withoutGlobalScopes()
                    ->whereKey($receipt->branch_id)
                    ->value('tenant_id');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'supplier_id' => null,
            'reference_no' => strtoupper(fake()->bothify('PO-####')),
            'received_by_user_id' => null,
            'received_at' => now(),
            'status' => StockReceiptStatus::Posted,
        ];
    }
}
