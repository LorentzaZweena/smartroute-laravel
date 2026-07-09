<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Jalankan Autoloader
require __DIR__.'/../vendor/autoload.php';

// Ambil instansiasi aplikasi asli
$app = require_once __DIR__.'/../bootstrap/app.php';

// --- TRICK PAMUNGKAS VERCEL SERVERLESS ---
// Paksa semua file manifest (packages, services, config) ditulis ke /tmp
if (isset($_SERVER['VERCEL_URL']) || isset($_ENV['VERCEL_URL'])) {
    $targetCacheDir = '/tmp/storage/bootstrap/cache';
    if (!file_exists($targetCacheDir)) {
        mkdir($targetCacheDir, 0755, true);
    }

    // Paksa Laravel mengubah lokasi penyimpanan bootstrap path secara runtime
    $app->useBootstrapPath($targetCacheDir);
    
    // Backup env cadangan demi keamanan internal framework
    putenv("APP_packages_CACHE={$targetCacheDir}/packages.php");
    putenv("APP_services_CACHE={$targetCacheDir}/services.php");
}
// -----------------------------------------

// Proses Request seperti biasa
$response = $app->handle(Request::capture());

$response->send();

$app->terminate(Request::capture(), $response);