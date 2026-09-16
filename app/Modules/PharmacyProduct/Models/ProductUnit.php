<?php

namespace App\Modules\PharmacyProduct\Models;

use App\Modules\PharmacyProduct\Domain\Enums\ProductStatus;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use Database\Factories\ProductUnitFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    /** @use HasFactory<ProductUnitFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'pharmacy_product_id',
        'name',
        'symbol',
        'multiplier_to_base',
        'is_base',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'multiplier_to_base' => 'decimal:6',
            'is_base' => 'boolean',
            'status' => ProductStatus::class,
        ];
    }

    /**
     * @return BelongsTo<PharmacyProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'pharmacy_product_id');
    }

    public function toBase(float $quantityInUnit): float
    {
        return $quantityInUnit * (float) $this->multiplier_to_base;
    }

    protected static function newFactory(): ProductUnitFactory
    {
        return ProductUnitFactory::new();
    }
}
