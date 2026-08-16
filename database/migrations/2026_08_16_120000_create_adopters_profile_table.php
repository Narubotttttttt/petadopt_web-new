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
        Schema::create('adopters_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Identification
            $table->string('adopter_code')->unique(); // e.g. ADP-0001
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Address & Residence
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            
            // Shelter Tracking & Safety (Item #2: Shelter Safety & Status Management)
            $table->enum('status', ['active', 'good_standing', 'restricted', 'blacklisted'])->default('active');
            $table->text('admin_notes')->nullable();
            
            // Check-in Compliance
            $table->date('last_check_in_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adopters_profile');
    }
};