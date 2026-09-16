<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('supplier_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->uuid('received_by_user_id')->nullable();
            $table->timestamp('received_at');
            $table->string('status', 32);
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'branch_id', 'received_at']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'supplier_id'])->references(['tenant_id', 'id'])->on('suppliers')->nullOnDelete();
        });

        Schema::create('stock_receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('stock_receipt_id');
            $table->uuid('pharmacy_product_id');
            $table->uuid('product_unit_id');
            $table->decimal('quantity_in_unit', 18, 3);
            $table->decimal('quantity_base', 18, 3);
            $table->string('batch_number');
            $table->timestamp('manufactured_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('unit_cost_base', 18, 6);
            $table->uuid('inventory_batch_id')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'stock_receipt_id']);
            $table->foreign(['tenant_id', 'stock_receipt_id'])->references(['tenant_id', 'id'])->on('stock_receipts')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'pharmacy_product_id'])->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'product_unit_id'])->references(['tenant_id', 'id'])->on('product_units')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'inventory_batch_id'])->references(['tenant_id', 'id'])->on('inventory_batches')->nullOnDelete();
        });

        DB::statement('ALTER TABLE stock_receipt_items ADD CONSTRAINT stock_receipt_items_quantity_positive CHECK (quantity_in_unit > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_receipt_items');
        Schema::dropIfExists('stock_receipts');
    }
};
