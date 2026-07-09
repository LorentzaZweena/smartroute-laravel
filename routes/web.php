<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RouteAiController;

Route::get('/', function () {
    return view('webgis');
});

Route::post('/api/calculate-route', [RouteAiController::class, 'calculateRoute']);