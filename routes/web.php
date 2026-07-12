<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\RouteAiController;

Route::get('/', function () {
    return view('webgis');
});

Route::post('/calculate-route', [RouteAiController::class, 'calculateRoute']);
Route::get('/mapid-style', function () {
    $apiKey = '0cd87839439d453e83fc7da1547fafdb';
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    ])->get("https://geo.mapid.io/tiles/v1/styles/default/style.json?key={$apiKey}");
    
    if ($response->successful()) {
        return response($response->json(), 200)
            ->header('Content-Type', 'application/json');
    }
    
    return response()->json(['error' => 'Gagal mengambil peta'], 500);
});