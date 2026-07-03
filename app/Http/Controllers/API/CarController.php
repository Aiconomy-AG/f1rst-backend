<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    // Return all cars to the frontend
    public function index()
    {
        return Car::all();
    }

    public function show(Car $car)
    {
        return $car;
    }

    // Save a new car to the database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'power' => 'required|integer'
        ]);

        return Car::create($validated);
    }

    // Update an existing car
    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'power' => 'required|integer'
        ]);

        $car->update($validated);
        return $car;
    }

    // Delete a car
    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json(['message' => 'Car deleted']);
    }

    // Aggregate car counts by cylinder (power)
    public function cylinderStats()
    {
        try {
            // Use standard Eloquent to avoid raw SQL grouping issues
            $stats = Car::select('power')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('power')
                ->orderBy('power', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'cylinders' => $item->power,
                        'count' => (int) $item->count
                    ];
                });

            return response()->json($stats);
        } catch (\Exception $e) {
            // Log the actual error to your laravel.log file
            \Log::error('CylinderStats Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
