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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();

            // The item being prescribed (e.g., medication)
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            // Quantities
            $table->integer('prescribed_quantity');
            $table->integer('dispensed_quantity')->default(0);

            // Instructions
            $table->string('dosage_instructions'); // e.g., '1 tablet every 8 hours'
            $table->integer('duration_days')->nullable();

            // Fulfillment Status
            $table->string('status')->default('pending'); // 'pending', 'filled', 'cancelled'

            // Reference to the inventory transaction if fulfilled
            $table->json('transaction_log')->nullable(); // Arrays of transaction IDs linked to this fulfillment

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
