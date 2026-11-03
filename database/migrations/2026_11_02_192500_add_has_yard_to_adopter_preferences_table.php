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
            $table->boolean('has_yard')->default(false)->after('living_environment');
            $table->string('yard_type')->nullable()->after('has_yard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adopter_preferences', function (Blueprint $table) {
            $table->dropColumn(['has_yard', 'yard_type']);
        });
    }
};

