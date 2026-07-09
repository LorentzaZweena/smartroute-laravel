<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class RouteAiController extends Controller
{
    public function calculateRoute(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $strukGoPath = public_path('data/struk-go.json');
        $strukGoData = "";
        
        if (File::exists($strukGoPath)) {
            $geoJson = json_decode(File::get($strukGoPath), true);
            $features = array_slice($geoJson['features'] ?? [], 0, 10);
            
            foreach ($features as $f) {
                $nama = $f['properties']['Nama_Tempat'] ?? 'Merchant';
                $kategori = $f['properties']['Kategori_Tempat'] ?? 'Umum';
                $coords = json_encode($f['geometry']['coordinates'] ?? []);
                $strukGoData .= "- {$nama} (Kategori: {$kategori}) di koordinat {$coords}\n";
            }
        }

        $prompt = "Kamu adalah sistem Kecerdasan Spasial canggih untuk aplikasi SmartRoute.\n";
        $prompt .= "Tugasmu adalah menganalisis rute intermodal transportasi massal dari '{$origin}' menuju '{$destination}'.\n\n";
        $prompt .= "Berikut adalah data titik transaksi pengeluaran (Struk Go dari MAPID) yang berada di sekitar wilayah analisis:\n";
        $prompt .= $strukGoData . "\n";
        $prompt .= "Berikan analisis rute yang efisien dalam 2-3 kalimat pendek, dan hubungkan bagaimana titik-titik Struk Go tersebut mencerminkan aktivitas ekonomi atau pergerakan penumpang di sekitar rute tersebut.\n";
        $prompt .= "Format output WAJIB dalam bentuk JSON valid dengan struktur: {\"text\": \"isi analisis kamu\", \"coordinates\": [[lng1, lat1], [lng2, lat2]]}";

        $apiKey = env('GEMINI_API_KEY');
        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            // Return raw text agar fungsi parsing JSON manual di javascript kamu tetap bekerja
            return response($aiText)->header('Content-Type', 'application/json');
        }

        return response()->json(['text' => 'Gagal terhubung ke Spatial AI.', 'coordinates' => []], 500);
    }
}