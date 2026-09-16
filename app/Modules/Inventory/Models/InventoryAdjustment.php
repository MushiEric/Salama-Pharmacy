<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Domain\Enums\AdjustmentReason;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use Database\Factories\InventoryAdjustmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    /** @use HasFactory<InventoryAdjustmentFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'pharmacy_product_id',
        'batch_id',
        'quantity_delta_base',
        'reason',
        'notes',
        'created_by_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_delta_base' => 'decimal:3',
            'reason' => AdjustmentReason::class,
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

    /**
     * @return BelongsTo<InventoryBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    protected static function newFactory(): InventoryAdjustmentFactory
    {
        return InventoryAdjustmentFactory::new();
    }
}
