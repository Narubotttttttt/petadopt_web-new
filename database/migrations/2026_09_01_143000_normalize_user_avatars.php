<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add avatar to staff_profiles if not present
        if (Schema::hasTable('staff_profiles') && !Schema::hasColumn('staff_profiles', 'avatar')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('full_name');
            });
        }

        // 2. Add avatar to adopters_profile if not present
        if (Schema::hasTable('adopters_profile') && !Schema::hasColumn('adopters_profile', 'avatar')) {
            Schema::table('adopters_profile', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('full_name');
            });
        }

        // 3. Migrate existing avatars from users table to respective profile tables
        if (Schema::hasColumn('users', 'avatar')) {
            $usersWithAvatar = DB::table('users')->whereNotNull('avatar')->where('avatar', '!=', '')->get();
            foreach ($usersWithAvatar as $u) {
                if ($u->role === 'adopter') {
                    DB::table('adopters_profile')
                        ->where('email', $u->email)
                        ->orWhere('user_id', $u->id)
                        ->update(['avatar' => $u->avatar]);
                } else {
                    // Admin / Staff
                    if (Schema::hasTable('staff_profiles')) {
                        DB::table('staff_profiles')
                            ->updateOrInsert(
                                ['user_id' => $u->id],
                                [
                                    'staff_code' => sprintf('STF-%04d', $u->id),
                                    'full_name' => $u->name,
                                    'avatar' => $u->avatar,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                    }
                }
            }

            // 4. Drop avatar column from users table
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable();
            });
        }
    }
};
