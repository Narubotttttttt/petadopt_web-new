<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adopter_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('preferred_species')->default('any'); // dog, cat, any
            $table->string('preferred_gender')->default('any');  // male, female, any
            $table->string('preferred_age')->default('any');     // kitten_puppy, young, adult, senior, any
            $table->string('preferred_color')->nullable();       // orange, black, white, etc., any
            $table->string('living_environment')->default('apartment'); // apartment, house_with_yard, house_no_yard
            $table->string('activity_level')->default('moderate');      // relaxed, moderate, high
            $table->string('pet_experience')->default('first_time');    // first_time, experienced
            $table->boolean('has_children')->default(false);
            $table->boolean('has_other_pets')->default(false);
            $table->string('hours_alone')->default('4_7');              // 0_3, 4_7, 8_plus
            $table->boolean('special_care_capacity')->default(false);
            $table->json('desired_temperaments')->nullable();           // ["Friendly", "Calm", ...]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adopter_preferences');
    }
};
