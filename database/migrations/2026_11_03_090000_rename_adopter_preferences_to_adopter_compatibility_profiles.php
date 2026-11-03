<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('adopter_preferences', 'adopter_compatibility_profiles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('adopter_compatibility_profiles', 'adopter_preferences');
    }
};

