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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // Financial & Rating
            $table->decimal('rating', 3, 2)->default(5.00); // Supplier performance 0-5 stars
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preferred')->default(false); // Used for automated POs

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        // Update items table to link to a preferred supplier
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('preferred_supplier_id')->nullable()->after('category_id')->constrained('suppliers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['preferred_supplier_id']);
            $table->dropColumn('preferred_supplier_id');
        });

        Schema::dropIfExists('suppliers');
    }
};
