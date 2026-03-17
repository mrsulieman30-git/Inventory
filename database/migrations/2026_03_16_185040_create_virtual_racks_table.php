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
        Schema::create('virtual_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., 'Aisle 3, Rack B'
            $table->string('bin_number')->nullable(); // Granular tracking down to the bin level

            // Storage Requirements
            $table->boolean('is_cold_storage')->default(false); // e.g., Refrigerator
            $table->boolean('is_secure')->default(false); // Locked cabinet for narcotics

            // Sorting/Picking Optimization
            $table->integer('sort_order')->default(0); // Determines the optimal walking route for the pharmacist
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['location_id', 'name', 'bin_number']);
        });

        // Add rack to batches to know exactly where stock lives
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('virtual_rack_id')->nullable()->after('location_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['virtual_rack_id']);
            $table->dropColumn('virtual_rack_id');
        });

        Schema::dropIfExists('virtual_racks');
    }
};
