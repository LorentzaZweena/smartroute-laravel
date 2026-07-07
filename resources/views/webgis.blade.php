<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartRoute AI - MAPID 2026</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        #map canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>
<body class="flex flex-col-reverse md:flex-row h-full w-full bg-slate-100 m-0 p-0 overflow-hidden">
    <div class="w-full md:w-96 bg-white shadow-2xl z-10 flex flex-col h-2/5 md:h-full border-t md:border-t-0 md:border-r border-slate-200 shrink-0">
        <div class="p-4 bg-slate-900 text-white">
            <h1 class="text-base font-bold">🗺️ SmartRoute Spatial AI</h1>
            <p class="text-[10px] text-cyan-400">Laravel Edition - MAPID Competition 2026</p>
        </div>

        <div class="p-4 space-y-4 flex-1 overflow-y-auto">
            <div class="space-y-2">
                <input type="text" id="origin" placeholder="Titik Asal (Contoh: Stasiun Tanah Abang)" class="w-full p-2 border rounded text-xs bg-slate-50" />
                <input type="text" id="destination" placeholder="Titik Tujuan (Contoh: Stasiun Cilebut)" class="w-full p-2 border rounded text-xs bg-slate-50" />
                <button onclick="prosesRuteAI()" id="btn-submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded transition">Analisis Rute Spasial</button>
            </div>

            <div class="p-3 bg-slate-50 border rounded-lg">
                <h3 class="font-bold text-xs text-slate-700 mb-2">Lapisan Data Geospasial</h3>
                <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer">
                    <input type="checkbox" id="layer-heatmap" checked class="w-4 h-4 text-indigo-600">
                    <span>Heatmap Kepadatan Penumpang (Data Survei)</span>
                </label>
            </div>

            <div class="p-3 bg-slate-900 text-slate-100 rounded-lg h-44 overflow-y-auto text-xs" id="ai-output">
                <span class="text-cyan-400 font-bold block mb-1">🤖 SPATIAL AI INSIGHT:</span>
                Silakan tentukan asal dan tujuan rute untuk memicu analisis kecerdasan spasial...
            </div>
        </div>
    </div>

    <div id="map" class="flex-1 h-3/5 md:h-full w-full relative z-0 min-h-[50vh]"></div>

    <script>
        const map = new maplibregl.Map({
            container: 'map',
            style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
            center: [106.8250, -6.2070],
            zoom: 12
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');

        map.on('load', () => {
            map.addSource('route-source', {
                type: 'geojson',
                data: { type: 'Feature', geometry: { type: 'LineString', coordinates: [] } }
            });

            map.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route-source',
                paint: { 'line-color': '#4f46e5', 'line-width': 5 }
            });

            map.on('click', 'route-line', (e) => {
                new maplibregl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML(`<div class="p-1 text-xs"><strong>💡 Info Jalur AI</strong><br>Rute dihitung berdasarkan efisiensi intermoda dan data penumpukan penumpang terintegrasi.</div>`)
                    .addTo(map);
            });

            map.on('mouseenter', 'route-line', () => map.getCanvas().style.cursor = 'pointer');
            map.on('mouseleave', 'route-line', () => map.getCanvas().style.cursor = '');
            setTimeout(() => { map.resize(); }, 300);
        });

        async function prosesRuteAI() {
            const origin = document.getElementById('origin').value;
            const destination = document.getElementById('destination').value;
            const showHeatmap = document.getElementById('layer-heatmap').checked;

            if (!origin || !destination) return alert('Mohon isi titik asal dan tujuan terlebih dahulu!');

            document.getElementById('btn-submit').innerText = "Memproses Kecerdasan AI...";
            document.getElementById('ai-output').innerHTML = `<span class="text-cyan-400 font-bold block mb-1">🤖 SPATIAL AI INSIGHT:</span>Sedangkan memproses rute...`;

            try {
                const response = await fetch('/api/calculate-route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ origin, destination, showHeatmap })
                });

                const rawText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(rawText);
                } catch (e) {
                    console.error("Gagal parse otomatis, mencoba membersihkan format markdown AI", e);
                    const cleanText = rawText.replace(/```json|```/g, '').trim();
                    data = JSON.parse(cleanText);
                }

                let outputInsight = "";
                if (data && data.text) {
                    outputInsight = data.text;
                } else if (typeof data === 'string') {
                    outputInsight = data;
                } else {
                    outputInsight = "Rute berhasil dipetakan, namun format ringkasan teks dari AI tidak sesuai standar.";
                }

                document.getElementById('ai-output').innerHTML = `<span class="text-cyan-400 font-bold block mb-1">🤖 SPATIAL AI INSIGHT:</span>${outputInsight}`;
                if (data.coordinates && data.coordinates.length > 0) {
                    map.getSource('route-source').setData({
                        type: 'Feature',
                        geometry: { type: 'LineString', coordinates: data.coordinates }
                    });

                    const bounds = data.coordinates.reduce((acc, coord) => acc.extend(coord), new maplibregl.LngLatBounds(data.coordinates[0], data.coordinates[0]));
                    map.fitBounds(bounds, { padding: 60, duration: 1500 });
                }
            } catch (error) {
                console.error("Error mengambil data spasial AI:", error);
                document.getElementById('ai-output').innerHTML = `<span class="text-cyan-400 font-bold block mb-1">🤖 SPATIAL AI INSIGHT:</span>Gagal memproses data rute dari server API.`;
            } finally {
                document.getElementById('btn-submit').innerText = "Analisis Rute Spasial";
            }
        }
    </script>
</body>
</html>