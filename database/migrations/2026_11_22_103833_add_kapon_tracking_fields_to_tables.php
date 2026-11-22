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
        Schema::table('pets', function (Blueprint $table) {
            $table->boolean('is_neutered')->default(false)->after('gender');
        });

        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->string('kapon_status')->default('not_required')->after('status');
            $table->dateTime('kapon_scheduled_date')->nullable()->after('kapon_status');
            $table->string('kapon_venue')->nullable()->after('kapon_scheduled_date');
            $table->text('kapon_instructions')->nullable()->after('kapon_venue');
            $table->dateTime('kapon_completed_at')->nullable()->after('kapon_instructions');
            $table->string('kapon_attending_vet')->nullable()->after('kapon_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn('is_neutered');
        });

        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropColumn([
                'kapon_status',
                'kapon_scheduled_date',
                'kapon_venue',
                'kapon_instructions',
                'kapon_completed_at',
                'kapon_attending_vet',
            ]);
        });
    }
};
