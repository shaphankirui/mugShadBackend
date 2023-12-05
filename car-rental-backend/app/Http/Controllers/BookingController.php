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
            'pickup_date' => 'required|date_format:m/d/Y',
            'dropoff_date' => 'required|date_format:m/d/Y',
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
    
        $pickupDate = date_create_from_format('m/d/Y', $request->input('pickup_date'));
        $dropoffDate = date_create_from_format('m/d/Y', $request->input('dropoff_date'));
    
        // Check if date conversion was successful
        if ($pickupDate && $dropoffDate) {
            $booking = Booking::create([
                'name' => $request->input('name'),
                'pickup_date' => $pickupDate->format('Y-m-d'),
                'dropoff_date' => $dropoffDate->format('Y-m-d'),
                // Include other fields...
            ]);
    
            return response()->json($booking, 201);
        } else {
            // Handle date conversion error
            return response()->json(['error' => 'Invalid date format'], 400);
        }
    }

    public function update(Request $request, $id) {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable',
            'pickup_date' => 'nullable|',
            'dropoff_date' => 'nullable',
            'pickup_location' => 'nullable',
            'dropoff_location' => 'nullable',
            'car_type' => 'required',
            'car_id' => 'nullable',
            'id_number' => 'nullable',
            'phone_number' => 'required',
            'license_plate' => 'nullable',
            'destination' => 'nullable',
        ]);

        
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $pickupDate = date_create_from_format('m/d/Y', $request->input('pickup_date'));
    $dropoffDate = date_create_from_format('m/d/Y', $request->input('dropoff_date'));

    // Check if date conversion was successful
    if ($pickupDate && $dropoffDate) {
        $booking = Booking::create([
            'name' => $request->input('name'),
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'dropoff_date' => $dropoffDate->format('Y-m-d'),
            // Include other fields...
        ]);

        return response()->json($booking, 201);
    } else {
        // Handle date conversion error
        return response()->json(['error' => 'Invalid date format'], 400);
    }
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
