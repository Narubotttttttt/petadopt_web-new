<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pets MODIFY COLUMN status ENUM('in_shelter', 'available', 'pending', 'adopted', 'deceased') NOT NULL DEFAULT 'in_shelter'");
        }

        Schema::table('pets', function (Blueprint $table) {
            if (!Schema::hasColumn('pets', 'rescue_date')) {
                $table->date('rescue_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('pets', 'rescue_location')) {
                $table->string('rescue_location')->nullable()->after('rescue_date');
            }
            if (!Schema::hasColumn('pets', 'intake_condition')) {
                $table->text('intake_condition')->nullable()->after('rescue_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            if (Schema::hasColumn('pets', 'intake_condition')) {
                $table->dropColumn('intake_condition');
            }
            if (Schema::hasColumn('pets', 'rescue_location')) {
                $table->dropColumn('rescue_location');
            }
            if (Schema::hasColumn('pets', 'rescue_date')) {
                $table->dropColumn('rescue_date');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pets MODIFY COLUMN status ENUM('available', 'pending', 'adopted', 'deceased') NOT NULL DEFAULT 'available'");
        }
    }
};
