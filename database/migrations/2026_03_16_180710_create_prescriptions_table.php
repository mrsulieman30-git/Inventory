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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();

            // Link to the patient and the prescribing doctor
            $table->string('patient_id')->index(); // Could be a UUID or a link to a Patient model in a full system
            $table->foreignId('prescriber_id')->constrained('users')->restrictOnDelete();

            // Fulfillment Details
            $table->string('prescription_number')->unique();
            $table->string('status')->default('pending'); // 'pending', 'partially_filled', 'filled', 'cancelled'

            // Pharmacy Location processing the prescription
            $table->foreignId('location_id')->constrained()->restrictOnDelete();

            $table->text('clinical_notes')->nullable();

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
        Schema::dropIfExists('prescriptions');
    }
};
