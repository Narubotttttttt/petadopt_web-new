<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('category', ['vaccination', 'deworming', 'treatment', 'checkup', 'surgery', 'injury_illness']);
            $table->text('description');
            $table->string('administered_by')->nullable();
            $table->date('next_due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_logs');
    }
};