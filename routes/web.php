<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('webgis');
});

Route::post('/api/calculate-route', [RouteAiController::class, 'calculateRoute']);