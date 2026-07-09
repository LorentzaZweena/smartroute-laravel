<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RouteAiController extends Controller
{
    public function calculateRoute(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $showHeatmap = $request->input('showHeatmap', false);

        $apiKey = env('GEMINI_KEY');
        if (!$apiKey) {
            return response()->json([
                'text' => '⚠️ Error: GEMINI_KEY belum diisi di file .env proyek kamu!',
                'coordinates' => [[106.8250, -6.2070]]
            ]);
        }

        $prompt = "Kamu adalah AI Spatial WebGIS MAPID 2026. Analisis rute terbaik dari '$origin' ke '$destination'. "
                . "Status Heatmap Kepadatan: " . ($showHeatmap ? 'AKTIF' : 'MATI') . ". "
                . "Berikan narasi analisis maksimal 3 kalimat. "
                . "WAJIB merespon hanya dengan format JSON murni seperti ini tanpa teks lain: "
                . "{\"text\": \"Tulis narasi analisis kamu di sini\", \"coordinates\": [[106.8250, -6.2070], [106.8494, -6.2099]]}";

        try {
            $response = Http::withoutVerifying()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->failed()) {
                return response()->json([
                    'text' => '❌ Gagal menghubungi Google AI API. Status: ' . $response->status(),
                    'coordinates' => []
                ]);
            }

            $resultData = $response->json();
            $rawText = $resultData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if (empty($rawText)) {
                return response()->json([
                    'text' => '⚠️ Google AI merespon, tetapi tidak ada teks analisis yang dihasilkan.',
                    'coordinates' => []
                ]);
            }

            $cleanData = json_decode(trim($rawText), true);
            if (json_last_error() === JSON_ERROR_NONE && isset($cleanData['text'])) {
                return response()->json($cleanData);
            }

            return response()->json([
                'text' => $rawText,
                'coordinates' => [[106.8250, -6.2070], [106.8494, -6.2099]]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'text' => '💥 Terjadi error internal server: ' . $e->getMessage(),
                'coordinates' => []
            ]);
        }
    }
}