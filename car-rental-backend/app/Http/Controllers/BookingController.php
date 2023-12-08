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
            'name' => 'nullable',
            'pickup_date' => 'nullable',
            'dropoff_date' => 'nullable',
            'pickup_location' => 'nullable',
            'dropoff_location' => 'nullable',
            'car_type' => 'nullable', // Allow car_type to be nullable
            'car_id' => 'nullable',
            'id_number' => 'nullable',
            'phone_number' => 'required',
            'license_plate' => 'nullable',
            'destination' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Convert date strings to the 'YYYY-MM-DD' format
        $pickupDate = $request->input('pickup_date') ? date('Y-m-d', strtotime($request->input('pickup_date'))) : null;
        $dropoffDate = $request->input('dropoff_date') ? date('Y-m-d', strtotime($request->input('dropoff_date'))) : null;

        $booking = Booking::create([
            'name' => $request->input('name'),
            'pickup_date' => $pickupDate,
            'dropoff_date' => $dropoffDate,
            'pickup_location' => $request->input('pickup_location', ''),
            'dropoff_location' => $request->input('dropoff_location', ''),
            'car_type' => $request->input('car_type', 'Default Car Type'),
            'car_id' => $request->input('car_id', null), // Assuming car_id is nullable
            'id_number' => $request->input('id_number', ''),
            'phone_number' => $request->input('phone_number', ''),
            'license_plate' => $request->input('license_plate', ''),
            'destination' => $request->input('destination', ''),
            // Other fields you may have in your 'bookings' table
        ]);
        

        return response()->json($booking, 201);
    }
    

    public function update(Request $request, $id) {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'pickup_date' => 'nullable',
            'dropoff_date' => 'nullable',
            'pickup_location' => 'nullable',
            'dropoff_location' => 'nullable',
            'car_type' => 'nullable', // Allow car_type to be nullable
            'car_id' => 'nullable',
            'id_number' => 'nullable',
            'phone_number' => 'required',
            'license_plate' => 'nullable',
            'destination' => 'nullable',
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
