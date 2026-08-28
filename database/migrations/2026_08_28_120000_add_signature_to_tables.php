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
        if (Schema::hasTable('adoption_applications')) {
            Schema::table('adoption_applications', function (Blueprint $table) {
                if (!Schema::hasColumn('adoption_applications', 'signature_path')) {
                    $table->string('signature_path')->nullable();
                }
                if (!Schema::hasColumn('adoption_applications', 'signed_at')) {
                    $table->timestamp('signed_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'digital_signature_path')) {
                    $table->string('digital_signature_path')->nullable();
                }
            });
        }

        if (Schema::hasTable('adopters_profile')) {
            Schema::table('adopters_profile', function (Blueprint $table) {
                if (!Schema::hasColumn('adopters_profile', 'digital_signature_path')) {
                    $table->string('digital_signature_path')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adoption_applications')) {
            Schema::table('adoption_applications', function (Blueprint $table) {
                $table->dropColumn(['signature_path', 'signed_at']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['digital_signature_path']);
            });
        }

        if (Schema::hasTable('adopters_profile')) {
            Schema::table('adopters_profile', function (Blueprint $table) {
                $table->dropColumn(['digital_signature_path']);
            });
        }
    }
};
