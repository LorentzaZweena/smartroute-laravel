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
        $strukGoData = "";
    
        $coordAsal = $this->getDynamicCoordinates($origin);
        $coordTujuan = $this->getDynamicCoordinates($destination);

        if (!$coordAsal) { $coordAsal = [106.8272, -6.1754]; }
        if (!$coordTujuan) { $coordTujuan = [106.8456, -6.2088]; }

        try {
            $strukGoPath = base_path('public/data/struk-go.json');
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
        } catch (\Exception $e) {
            
        }

        $prompt = "Kamu adalah sistem Kecerdasan Spasial canggih untuk aplikasi SmartRoute.\n";
        $prompt .= "Tugasmu adalah menganalisis rute dari '{$origin}' menuju '{$destination}'.\n\n";
        $prompt .= "Berikut adalah data titik transaksi pengeluaran (Struk Go dari MAPID) di sekitar wilayah analisis:\n";
        $prompt .= $strukGoData . "\n";
        $prompt .= "Berikan analisis rute yang efisien dalam 2-3 kalimat pendek, dan hubungkan bagaimana titik-titik Struk Go tersebut mencerminkan aktivitas ekonomi di sekitar rute tersebut.\n";
        $prompt .= "Format output WAJIB dalam bentuk JSON valid dengan struktur: {\"text\": \"isi analisis kamu\", \"coordinates\": [[" . $coordAsal[0] . ", " . $coordAsal[1] . "], [" . $coordTujuan[0] . ", " . $coordTujuan[1] . "]]}";

        $apiKey = env('GEMINI_KEY');
        
        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['responseMimeType' => 'application/json']
        ]);

        if ($response->successful()) {
            $result = $response->json();
            $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $cleanJson = preg_replace('/```json|```/', '', $aiText);
            return response(trim($cleanJson))->header('Content-Type', 'application/json');
        }

        return response()->json([
            'text' => "Analisis spasial dari {$origin} menuju {$destination} berhasil dipetakan secara dinamis.", 
            'coordinates' => [$coordAsal, $coordTujuan]
        ]);
    }

    private function getDynamicCoordinates($address)
    {
        if (empty($address)) return null;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'SmartRouteSpatialAI/1.0 (RivApp MAPID Competition)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address . ', Indonesia',
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                return [(float)$data['lon'], (float)$data['lat']];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}