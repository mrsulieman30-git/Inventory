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
        Schema::create('drug_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('secondary_item_id')->constrained('items')->cascadeOnDelete();

            // Interaction Details
            $table->string('severity')->default('moderate'); // 'mild', 'moderate', 'severe', 'contraindicated'
            $table->text('description')->nullable(); // "Combination may cause severe hypotention."
            $table->text('clinical_recommendation')->nullable(); // "Monitor blood pressure closely or switch to alternative."

            // Who added this rule
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Prevent duplicate pairs A-B and B-A
            $table->unique(['primary_item_id', 'secondary_item_id']);
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_interactions');
    }
};
