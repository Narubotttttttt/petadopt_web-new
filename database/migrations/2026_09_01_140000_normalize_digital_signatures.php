<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add digital_signature_path to staff_profiles if not present
        if (Schema::hasTable('staff_profiles') && !Schema::hasColumn('staff_profiles', 'digital_signature_path')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->string('digital_signature_path')->nullable()->after('specialization');
            });
        }

        // 2. Ensure adopters_profile has digital_signature_path
        if (Schema::hasTable('adopters_profile') && !Schema::hasColumn('adopters_profile', 'digital_signature_path')) {
            Schema::table('adopters_profile', function (Blueprint $table) {
                $table->string('digital_signature_path')->nullable();
            });
        }

        // 3. Migrate existing signatures from users table into respective profile tables
        if (Schema::hasColumn('users', 'digital_signature_path')) {
            $usersWithSig = DB::table('users')->whereNotNull('digital_signature_path')->where('digital_signature_path', '!=', '')->get();
            foreach ($usersWithSig as $u) {
                if ($u->role === 'adopter') {
                    DB::table('adopters_profile')
                        ->where('email', $u->email)
                        ->orWhere('user_id', $u->id)
                        ->update(['digital_signature_path' => $u->digital_signature_path]);
                } else {
                    // Admin / Staff
                    if (Schema::hasTable('staff_profiles')) {
                        DB::table('staff_profiles')
                            ->updateOrInsert(
                                ['user_id' => $u->id],
                                [
                                    'staff_code' => sprintf('STF-%04d', $u->id),
                                    'full_name' => $u->name,
                                    'digital_signature_path' => $u->digital_signature_path,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                    }
                }
            }

            // 4. Drop redundant digital_signature_path column from users table
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('digital_signature_path');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'digital_signature_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('digital_signature_path')->nullable();
            });
        }
    }
};
