<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\PharmacyProduct\Models\ProductUnit;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use Database\Factories\StockReceiptItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceiptItem extends Model
{
    /** @use HasFactory<StockReceiptItemFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'stock_receipt_id',
        'pharmacy_product_id',
        'product_unit_id',
        'quantity_in_unit',
        'quantity_base',
        'batch_number',
        'manufactured_at',
        'expires_at',
        'unit_cost_base',
        'inventory_batch_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_in_unit' => 'decimal:3',
            'quantity_base' => 'decimal:3',
            'manufactured_at' => 'datetime',
            'expires_at' => 'datetime',
            'unit_cost_base' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<StockReceipt, $this>
     */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class, 'stock_receipt_id');
    }

    /**
     * @return BelongsTo<PharmacyProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'pharmacy_product_id');
    }

    /**
     * @return BelongsTo<ProductUnit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    /**
     * @return BelongsTo<InventoryBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    protected static function newFactory(): StockReceiptItemFactory
    {
        return StockReceiptItemFactory::new();
    }
}
