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
        Schema::create('pharmacy_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('master_drug_id')->nullable();
            $table->string('local_name');
            $table->boolean('prescription_required')->default(false);
            $table->string('status', 32);
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'status']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('master_drug_id')->references('id')->on('master_drugs')->nullOnDelete();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('pharmacy_product_id');
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->decimal('multiplier_to_base', 18, 6);
            $table->boolean('is_base')->default(false);
            $table->string('status', 32);
            $table->timestamps();

            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'pharmacy_product_id']);
            $table->foreign(['tenant_id', 'pharmacy_product_id'])
                ->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE product_units ADD CONSTRAINT product_units_multiplier_positive CHECK (multiplier_to_base > 0)');
        DB::statement('CREATE UNIQUE INDEX product_units_one_base_per_product ON product_units (pharmacy_product_id) WHERE is_base = true');

        Schema::create('product_branch_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('pharmacy_product_id');
            $table->decimal('selling_price', 18, 2);
            $table->decimal('reorder_level_base', 18, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'pharmacy_product_id']);
            $table->foreign(['tenant_id', 'branch_id'])
                ->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'pharmacy_product_id'])
                ->references(['tenant_id', 'id'])->on('pharmacy_products')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE product_branch_settings ADD CONSTRAINT product_branch_settings_price_non_negative CHECK (selling_price >= 0)');
        DB::statement('ALTER TABLE product_branch_settings ADD CONSTRAINT product_branch_settings_reorder_non_negative CHECK (reorder_level_base >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_branch_settings');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('pharmacy_products');
    }
};
