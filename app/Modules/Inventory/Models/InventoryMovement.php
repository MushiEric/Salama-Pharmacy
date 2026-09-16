<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Domain\Enums\InventoryMovementType;
use App\Modules\PharmacyProduct\Models\PharmacyProduct;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
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
        'type',
        'quantity_delta_base',
        'reference_type',
        'reference_id',
        'reason',
        'actor_user_id',
        'occurred_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InventoryMovementType::class,
            'quantity_delta_base' => 'decimal:3',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
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
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    protected static function newFactory(): InventoryMovementFactory
    {
        return InventoryMovementFactory::new();
    }
}
