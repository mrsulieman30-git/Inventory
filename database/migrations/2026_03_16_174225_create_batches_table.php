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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');

            // Stock details
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);

            // Dates
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Provider info
            $table->string('supplier_name')->nullable();

            // Tracking
            $table->string('status')->default('available'); // 'available', 'quarantined', 'expired', 'depleted'
            $table->foreignId('location_id')->constrained()->restrictOnDelete();

            $table->timestamps();

            // Indexes
            $table->unique(['item_id', 'batch_number', 'location_id']); // A batch can exist in multiple locations but only one entry per location
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
