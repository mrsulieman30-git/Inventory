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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment('Stock Keeping Unit code');
            $table->string('name');
            $table->text('description')->nullable();

            // Categorization
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('type')->default('general'); // 'medication', 'lab_reagent', 'consumable'
            $table->string('unit_of_measure'); // 'box', 'vial', 'tablet', 'ml', 'piece'

            // Pharmacy specific
            $table->boolean('is_prescription_required')->default(false);
            $table->boolean('is_controlled_substance')->default(false);
            $table->string('generic_name')->nullable();
            $table->string('strength')->nullable(); // e.g., '500mg'
            $table->string('form')->nullable(); // e.g., 'tablet', 'injection'

            // Lab specific
            $table->boolean('requires_cold_chain')->default(false);
            $table->string('storage_conditions')->nullable();

            // Settings
            $table->integer('minimum_stock_level')->default(0);
            $table->integer('maximum_stock_level')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('sku');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
