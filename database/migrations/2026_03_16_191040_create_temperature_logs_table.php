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
        Schema::create('temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_rack_id')->constrained()->cascadeOnDelete();

            // IoT Data payload
            $table->decimal('temperature_celsius', 5, 2);
            $table->decimal('humidity_percentage', 5, 2)->nullable();

            $table->string('status')->default('normal'); // 'normal', 'warning', 'critical'
            $table->text('system_action')->nullable(); // Log what action the SaaS took (e.g., 'Quarantined 5 batches')

            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['virtual_rack_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temperature_logs');
    }
};
