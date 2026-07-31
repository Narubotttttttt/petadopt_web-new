<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dateTime('approved_at')->nullable()->after('scheduled_at');
        });
    }

    public function down()
    {
        Schema::table('adoption_applications', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};