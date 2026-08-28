<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta para recibir los datos del ESP32 del invernadero
Route::post('/sensores/guardar', [SensorController::class, 'almacenar']);