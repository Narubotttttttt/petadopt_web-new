<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->foreignId('added_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->string('added_by_name')->nullable()->after('added_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->dropForeign(['added_by_user_id']);
            $table->dropColumn(['added_by_user_id', 'added_by_name']);
        });
    }
};
