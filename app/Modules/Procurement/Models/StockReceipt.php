<?php

namespace App\Modules\Procurement\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Procurement\Domain\Enums\StockReceiptStatus;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Tenancy\Infrastructure\Persistence\BelongsToTenant;
use App\Modules\Tenancy\Models\Branch;
use Database\Factories\StockReceiptFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    /** @use HasFactory<StockReceiptFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'supplier_id',
        'reference_no',
        'received_by_user_id',
        'received_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'status' => StockReceiptStatus::class,
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
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    /**
     * @return HasMany<StockReceiptItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }

    protected static function newFactory(): StockReceiptFactory
    {
        return StockReceiptFactory::new();
    }
}
