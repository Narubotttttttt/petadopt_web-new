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
        if (Schema::hasColumn('adopter_compatibility_profiles', 'desired_temperaments')) {
            Schema::table('adopter_compatibility_profiles', function (Blueprint $table) {
                $table->dropColumn('desired_temperaments');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('adopter_compatibility_profiles', 'desired_temperaments')) {
            Schema::table('adopter_compatibility_profiles', function (Blueprint $table) {
                $table->json('desired_temperaments')->nullable()->after('special_care_capacity');
            });
        }
    }
};
