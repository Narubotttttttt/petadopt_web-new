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
        Schema::table('medical_logs', function (Blueprint $table) {
            $table->boolean('is_external')->default(false)->after('administered_by');
            $table->string('clinic_name', 255)->nullable()->after('is_external');
            $table->string('proof_photo_path', 255)->nullable()->after('clinic_name');
            $table->string('verification_status', 50)->default('verified')->after('proof_photo_path');
            $table->foreignId('verified_by')->nullable()->after('verification_status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_logs', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'is_external',
                'clinic_name',
                'proof_photo_path',
                'verification_status',
                'verified_by',
                'verified_at',
            ]);
        });
    }
};

