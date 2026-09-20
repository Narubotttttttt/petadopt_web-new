<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff_profiles MODIFY COLUMN status ENUM('active', 'on_leave', 'inactive', 'deactivated') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff_profiles MODIFY COLUMN status ENUM('active', 'on_leave', 'inactive') NOT NULL DEFAULT 'active'");
        }
    }
};
