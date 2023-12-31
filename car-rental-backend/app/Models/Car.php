<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = ['car_name', 'properties', 'picture'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'car_id');
    }
}
