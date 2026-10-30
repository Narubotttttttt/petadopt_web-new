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
            $table->string('home_ownership', 50)->default('owned')->after('id_type');
            $table->string('landlord_name', 255)->nullable()->after('home_ownership');
            $table->string('landlord_phone', 50)->nullable()->after('landlord_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropColumn(['home_ownership', 'landlord_name', 'landlord_phone']);
        });
    }
};

