<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class DriverVehicleController extends Controller
{
    // Получить список техники текущего водителя
    public function index(): JsonResponse
    {
        $vehicles = auth()->user()->vehicles;
        return response()->json($vehicles);
    }

    // Добавить технику
    public function store(VehicleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $vehicle = auth()->user()->vehicles()->create($validated);

        return response()->json($vehicle, 201);
    }

    // Удалить технику
    public function destroy($id): JsonResponse
    {
        $vehicle = Vehicle::where('user_id', auth()->id())->findOrFail($id);
        $vehicle->delete();

        return response()->json(null, 204);
    }
}