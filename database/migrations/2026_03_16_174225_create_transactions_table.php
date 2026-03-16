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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Reference
            $table->string('reference_number')->unique();
            $table->string('type'); // 'stock_in', 'stock_out', 'transfer', 'adjustment', 'dispense'

            // Relation
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();

            // Locations involved
            $table->foreignId('source_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('locations')->nullOnDelete();

            // Quantities
            $table->integer('quantity');
            $table->integer('balance_after')->nullable(); // Balance at the location after transaction

            // Details
            $table->text('reason')->nullable(); // Why the adjustment or transfer was made
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // The user who performed it

            // Financials
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);

            $table->timestamps();

            // Indexing for analytics
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
