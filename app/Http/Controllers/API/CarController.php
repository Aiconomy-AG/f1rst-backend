<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarController extends Controller
{
    // 1. Filter the table data
    public function index()
    {
        $userId = Auth::id();

        // Return cars that belong to the user OR have no user
        return Car::whereNull('user_id')
            ->orWhere('user_id', $userId)
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'power' => 'required|integer',
        ]);

        // Automatically assign the car to the logged-in user
        $validated['user_id'] = Auth::id();

        $car = Car::create($validated);
        return response()->json($car, 201);
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        // Security: Prevent editing if the car belongs to someone else
        if ($car->user_id !== null && $car->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'power' => 'required|integer',
        ]);

        $car->update($validated);
        return response()->json($car);
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);

        // Security: Prevent deletion if the car belongs to someone else
        if ($car->user_id !== null && $car->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $car->delete();
        return response()->json(null, 204);
    }

    // 2. Filter the chart data
    public function cylinderStats()
    {
        $userId = Auth::id();

        $stats = Car::whereNull('user_id')
            ->orWhere('user_id', $userId)
            ->select('power')
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
    }
}
