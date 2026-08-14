<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_health_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adoption_application_id')->nullable()->constrained('adoption_applications')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('photo_path');
            $table->string('health_status')->default('healthy');
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('check_in_date');
            $table->string('status')->default('submitted');
            $table->text('staff_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_health_updates');
    }
};
