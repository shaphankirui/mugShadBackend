<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller {
    public function index() {
        $bookings = Booking::all();
        return response()->json($bookings);
    }

    public function show($id) {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        return response()->json($booking);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'pickup_date' => 'required|date',
            'dropoff_date' => 'required|date',
            'pickup_location' => 'required',
            'dropoff_location' => 'required',
            'car_type' => 'required',
            'car_id' => 'required|exists:cars,id',
            'id_number' => 'required',
            'phone_number' => 'required',
            'license_plate' => 'required',
            'destination' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $booking = Booking::create($request->all());

        return response()->json($booking, 201);
    }

    public function update(Request $request, $id) {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'pickup_date' => 'required|date',
            'dropoff_date' => 'required|date',
            'pickup_location' => 'required',
            'dropoff_location' => 'required',
            'car_type' => 'required',
            'car_id' => 'required|exists:cars,id',
            'id_number' => 'required',
            'phone_number' => 'required',
            'license_plate' => 'nullable',
            'destination' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $booking->update($request->all());

        return response()->json($booking, 200);
    }

    public function destroy($id) {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->delete();
        return response()->json(['message' => 'Booking deleted successfully'], 200);
    }
}
