<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['dog', 'cat'])->nullable();
            $table->string('age')->nullable();
            $table->string('breed')->nullable();
            $table->string('color')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('temperament')->nullable();
            $table->enum('status', ['available', 'pending', 'adopted'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pets');
    }
};