<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('generic_drugs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('status', 32);
            $table->timestamps();
        });

        Schema::create('master_drugs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('generic_drug_id');
            $table->string('brand_name');
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->string('category')->nullable();
            $table->string('status', 32);
            $table->timestamps();

            $table->index(['generic_drug_id', 'status']);
            $table->foreign('generic_drug_id')->references('id')->on('generic_drugs')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_drugs');
        Schema::dropIfExists('generic_drugs');
    }
};
