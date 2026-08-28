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
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->string('staff_signature_path')->nullable()->after('signed_at');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete()->after('staff_signature_path');
            $table->string('staff_name')->nullable()->after('staff_id');
            $table->timestamp('staff_signed_at')->nullable()->after('staff_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['staff_signature_path', 'staff_id', 'staff_name', 'staff_signed_at']);
        });
    }
};
