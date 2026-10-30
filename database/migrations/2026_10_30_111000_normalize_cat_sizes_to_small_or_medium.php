<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize any cats with 'large' size to 'medium'
        DB::table('pets')
            ->where('type', 'cat')
            ->where('size', 'large')
            ->update(['size' => 'medium']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed as cats should not be large
    }
};

