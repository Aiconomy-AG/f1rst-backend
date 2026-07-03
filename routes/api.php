<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\AuthController;

Route::post('/login', [AuthController::class, 'login']);
// routes/api.php
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/cars/cylinder-stats', [CarController::class, 'cylinderStats']);
    Route::apiResource('cars', CarController::class);
});
