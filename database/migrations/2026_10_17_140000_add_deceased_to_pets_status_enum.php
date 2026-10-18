<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pets MODIFY COLUMN status ENUM('available', 'pending', 'adopted', 'deceased') NOT NULL DEFAULT 'available'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pets MODIFY COLUMN status ENUM('available', 'pending', 'adopted') NOT NULL DEFAULT 'available'");
        }
    }
};
