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
            $table->string('employment_status', 50)->default('employed')->after('home_ownership');
            $table->string('occupation_or_program', 255)->nullable()->after('employment_status');
            $table->string('employer_or_school', 255)->nullable()->after('occupation_or_program');
        });

        Schema::table('adopters_profile', function (Blueprint $table) {
            $table->string('employment_status', 50)->nullable()->after('province');
            $table->string('occupation_or_program', 255)->nullable()->after('employment_status');
            $table->string('employer_or_school', 255)->nullable()->after('occupation_or_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'occupation_or_program', 'employer_or_school']);
        });

        Schema::table('adopters_profile', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'occupation_or_program', 'employer_or_school']);
        });
    }
};

