<?php

use App\Http\Controllers\API\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// routes/api.php
// 1. Place the specific custom route ABOVE the resource route
Route::get('/cars/cylinder-stats', [CarController::class, 'cylinderStats']);

// 2. Then define your resource route
Route::apiResource('cars', CarController::class);
