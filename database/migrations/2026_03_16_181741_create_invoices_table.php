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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();

            // Financial Status
            $table->string('status')->default('unpaid'); // 'unpaid', 'partially_paid', 'paid', 'void'

            // Payment Tracking
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('currency')->default('USD');

            // Dates
            $table->date('invoice_date');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); // User who verified GRN vs Invoice

            $table->text('discrepancy_notes')->nullable(); // For Three-way matching (PO vs GRN vs Invoice) discrepancies

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
