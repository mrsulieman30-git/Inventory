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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // 'created', 'updated', 'deleted', 'approved', 'rejected'
            $table->string('model_type'); // The fully qualified class name e.g., 'App\Models\PurchaseOrder'
            $table->unsignedBigInteger('model_id'); // The ID of the affected model

            // User tracking
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // The user performing the action
            $table->string('ip_address')->nullable();

            // Diff tracking for immutable logging
            $table->json('old_values')->nullable(); // Snapshot of what the record looked like before
            $table->json('new_values')->nullable(); // Snapshot of the new changes

            // Environment metadata
            $table->string('user_agent')->nullable();
            $table->text('reason')->nullable(); // Optional reason provided for the change (e.g., overriding alert)

            $table->timestamps(); // The 'when'

            $table->index(['model_type', 'model_id']);
            $table->index('event_type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
