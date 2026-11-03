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
        Schema::table('adopter_compatibility_profiles', function (Blueprint $table) {
            $table->unsignedInteger('revision_count')->default(0)->after('desired_temperaments');
            $table->timestamp('next_retake_available_at')->nullable()->after('revision_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adopter_compatibility_profiles', function (Blueprint $table) {
            $table->dropColumn(['revision_count', 'next_retake_available_at']);
        });
    }
};

