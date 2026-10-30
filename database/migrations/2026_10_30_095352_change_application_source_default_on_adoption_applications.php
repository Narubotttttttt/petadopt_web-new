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
            $table->string('application_source')->default('recommendation')->change();
        });

        \Illuminate\Support\Facades\DB::table('adoption_applications')
            ->where('application_source', 'manual_browsing')
            ->update(['application_source' => 'recommendation']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->string('application_source')->default('manual_browsing')->change();
        });
    }
};
