<?php

namespace App\Http\Controllers;
use App\Models\Car;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CarController extends Controller
{
    public function index() {
        $cars = Car::all();
        return response()->json($cars);
    }

    public function show($id) {
        $car = Car::find($id);
        if (!$car) {
            return response()->json(['error' => 'Car not found'], 404);
        }
        return response()->json($car);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'car_name' => 'required',
            'properties' => 'nullable',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $car = Car::create($request->all());

        return response()->json($car, 201);
    }

    public function update(Request $request, $id) {
        $car = Car::find($id);
        if (!$car) {
            return response()->json(['error' => 'Car not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'car_name' => 'required',
            'properties' => 'nullable',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $car->update($request->all());

        return response()->json($car, 200);
    }

    public function destroy($id) {
        $car = Car::find($id);
        if (!$car) {
            return response()->json(['error' => 'Car not found'], 404);
        }

        $car->delete();
        return response()->json(['message' => 'Car deleted successfully'], 200);
    }

    public function search(Request $request) {
        $search = $request->get('search');
        $cars = Car::where('car_name', 'like', '%' . $search . '%')->get();
        return response()->json($cars);
    }
}
