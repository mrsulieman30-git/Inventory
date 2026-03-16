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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            // Financial Line Info
            $table->decimal('unit_cost', 12, 2)->default(0); // Cost negotiated
            $table->integer('ordered_quantity');
            $table->decimal('total_price', 12, 2)->default(0); // unit_cost * ordered_quantity

            // Fulfillment Tracking
            $table->integer('received_quantity')->default(0);

            // Expected
            $table->string('status')->default('pending'); // 'pending', 'partially_received', 'fulfilled', 'cancelled'

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
