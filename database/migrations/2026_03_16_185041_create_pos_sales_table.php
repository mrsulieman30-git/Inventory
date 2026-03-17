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
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('location_id')->constrained()->restrictOnDelete(); // Store/Branch

            // Optional: The patient if a profile exists, otherwise generic guest
            $table->string('patient_id')->nullable()->index();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();

            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Payment method
            $table->string('payment_method')->default('cash'); // 'cash', 'card', 'insurance', 'split'

            // Workflow status
            $table->string('status')->default('completed'); // 'completed', 'refunded', 'voided'

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at'); // Used heavily for Z-reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
