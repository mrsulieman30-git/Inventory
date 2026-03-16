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
        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_sale_id')->constrained()->cascadeOnDelete();

            // The item being sold
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            // Link to the specific prescription if it's a fulfilled prescription (vs. an OTC sale)
            $table->foreignId('prescription_item_id')->nullable()->constrained()->nullOnDelete();

            // Sale details
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0); // The price at the time of sale
            $table->decimal('discount', 10, 2)->default(0); // Line-item specific discount
            $table->decimal('line_total', 10, 2)->default(0);

            // Reference to the inventory transactions handling the stock deduction
            $table->json('transaction_log')->nullable();

            $table->string('status')->default('sold'); // 'sold', 'refunded'

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sale_items');
    }
};
