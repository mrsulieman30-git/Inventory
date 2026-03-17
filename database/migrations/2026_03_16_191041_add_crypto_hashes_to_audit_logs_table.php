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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('previous_hash')->nullable()->after('reason');
            $table->string('hash')->nullable()->after('previous_hash');
            // Adding a digital signature to trace exactly who triggered it via public/private keys
            $table->text('signature')->nullable()->after('hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['previous_hash', 'hash', 'signature']);
        });
    }
};
