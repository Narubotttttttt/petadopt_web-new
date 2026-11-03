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
        Schema::table('adopter_preferences', function (Blueprint $table) {
            $table->dropColumn(['preferred_gender', 'preferred_age', 'preferred_color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adopter_preferences', function (Blueprint $table) {
            $table->string('preferred_gender')->default('any')->after('preferred_species');
            $table->string('preferred_age')->default('any')->after('preferred_gender');
            $table->string('preferred_color')->nullable()->after('preferred_age');
        });
    }
};

