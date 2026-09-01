<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('staff_code')->unique();
            $table->string('full_name');
            $table->string('position_title')->default('CAWS Staff Member');
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'on_leave', 'inactive'])->default('active');
            $table->string('specialization')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
