<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE adoption_applications MODIFY status ENUM('under_review', 'pending', 'approved', 'rejected') DEFAULT 'under_review'");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE adoption_applications MODIFY status ENUM('under_review', 'approved', 'rejected') DEFAULT 'under_review'");
        }
    }
};