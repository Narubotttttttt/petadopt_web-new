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
            if (!Schema::hasColumn('adoption_applications', 'id_type')) {
                $table->string('id_type', 100)->nullable()->after('applicant_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            if (Schema::hasColumn('adoption_applications', 'id_type')) {
                $table->dropColumn('id_type');
            }
        });
    }
};
