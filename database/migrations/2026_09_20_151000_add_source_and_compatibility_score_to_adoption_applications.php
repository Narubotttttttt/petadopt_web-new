<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->string('application_source')->default('manual_browsing')->after('status');
            $table->decimal('compatibility_score', 5, 2)->nullable()->after('application_source');
        });
    }

    public function down(): void
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropColumn(['application_source', 'compatibility_score']);
        });
    }
};
