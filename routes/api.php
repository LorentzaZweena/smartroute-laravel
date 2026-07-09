<?php

use Illuminate\Http\Request;
use App\Http\Controllers\RouteAiController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/calculate-route', [RouteAiController::class, 'calculateRoute']);
