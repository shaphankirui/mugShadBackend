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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('pickup_date');
            $table->date('dropoff_date');
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->string('car_type');
            $table->foreignId('car_id')->constrained('cars');
            $table->string('id_number');
            $table->string('phone_number');
            $table->string('license_plate');
            $table->string('destination');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('bookings');
    }
};
