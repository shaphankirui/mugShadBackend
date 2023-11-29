<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('car_name');
            $table->json('properties')->nullable();
            // Add other relevant columns
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('cars');
    }
};
