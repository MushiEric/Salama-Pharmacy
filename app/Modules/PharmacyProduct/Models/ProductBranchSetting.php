<?php

namespace App\Modules\PharmacyProduct\Models;

use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use Database\Factories\ProductBranchSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBranchSetting extends Model
{
    /** @use HasFactory<ProductBranchSettingFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'pharmacy_product_id',
        'selling_price',
        'reorder_level_base',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'reorder_level_base' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<PharmacyProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'pharmacy_product_id');
    }

    public function isLowStock(float $availableQuantityBase): bool
    {
        return $availableQuantityBase <= (float) $this->reorder_level_base;
    }

    protected static function newFactory(): ProductBranchSettingFactory
    {
        return ProductBranchSettingFactory::new();
    }
}
