<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'pickup_date',
        'dropoff_date',
        'pickup_location',
        'dropoff_location',
        'car_type',
        'car_id',
        'id_number',
        'phone_number',
        'license_plate',
        'destination',
    ];

    public function car() {
        return $this->belongsTo(Car::class);
    }
}
