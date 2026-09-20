<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert any existing on_leave or inactive to active or deactivated
        DB::table('staff_profiles')->where('status', 'on_leave')->update(['status' => 'active']);
        DB::table('staff_profiles')->where('status', 'inactive')->update(['status' => 'deactivated']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff_profiles MODIFY COLUMN status ENUM('active', 'deactivated') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff_profiles MODIFY COLUMN status ENUM('active', 'on_leave', 'inactive', 'deactivated') NOT NULL DEFAULT 'active'");
        }
    }
};
