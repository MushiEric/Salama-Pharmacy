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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('pharmacy_product_id');
            $table->uuid('supplier_id')->nullable();
            $table->string('batch_number');
            $table->timestamp('received_at');
            $table->timestamp('manufactured_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('initial_quantity_base', 18, 3);
            $table->decimal('available_quantity_base', 18, 3);
            $table->decimal('unit_cost_base', 18, 6);
            $table->string('status', 32);
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'branch_id', 'pharmacy_product_id']);
            $table->index(['tenant_id', 'branch_id', 'pharmacy_product_id', 'expires_at']);
            $table->index(['tenant_id', 'branch_id', 'expires_at']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'pharmacy_product_id'])->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'supplier_id'])->references(['tenant_id', 'id'])->on('suppliers')->nullOnDelete();
        });

        DB::statement('ALTER TABLE inventory_batches ADD CONSTRAINT inventory_batches_available_non_negative CHECK (available_quantity_base >= 0)');
        DB::statement('ALTER TABLE inventory_batches ADD CONSTRAINT inventory_batches_initial_non_negative CHECK (initial_quantity_base >= 0)');

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('pharmacy_product_id');
            $table->uuid('batch_id')->nullable();
            $table->string('type', 32);
            $table->decimal('quantity_delta_base', 18, 3);
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->string('reason')->nullable();
            $table->uuid('actor_user_id')->nullable();
            $table->timestamp('occurred_at');
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['tenant_id', 'branch_id', 'pharmacy_product_id', 'occurred_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'pharmacy_product_id'])->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'batch_id'])->references(['tenant_id', 'id'])->on('inventory_batches')->nullOnDelete();
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('pharmacy_product_id');
            $table->uuid('batch_id');
            $table->decimal('quantity_delta_base', 18, 3);
            $table->string('reason', 32);
            $table->text('notes')->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->timestamp('created_at');

            $table->index(['tenant_id', 'branch_id', 'pharmacy_product_id']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'pharmacy_product_id'])->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'batch_id'])->references(['tenant_id', 'id'])->on('inventory_batches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_batches');
    }
};
