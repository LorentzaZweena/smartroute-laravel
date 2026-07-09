<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Jalankan Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Ambil instansiasi aplikasi asli
$app = require_once __DIR__ . '/../bootstrap/app.php';

// --- TRICK PAMUNGKAS VERCEL SERVERLESS ---
if (isset($_SERVER['VERCEL_URL']) || isset($_ENV['VERCEL_URL'])) {
    $baseBootstrapDir = '/tmp/storage/bootstrap';
    $actualCacheDir = '/tmp/storage/bootstrap/cache';

    if (!file_exists($actualCacheDir)) {
        mkdir($actualCacheDir, 0755, true);
    }

    $app->useBootstrapPath($baseBootstrapDir);

    putenv("APP_PACKAGES_CACHE={$actualCacheDir}/packages.php");
    putenv("APP_SERVICES_CACHE={$actualCacheDir}/services.php");
    $_ENV['APP_PACKAGES_CACHE'] = "{$actualCacheDir}/packages.php";
    $_ENV['APP_SERVICES_CACHE'] = "{$actualCacheDir}/services.php";

    // --- OTOMATISASI SQLITE DI VERCEL ---
    $sqlitePath = '/tmp/database.sqlite';
    if (!file_exists($sqlitePath)) {
        touch($sqlitePath); // Buat file database kosong di /tmp
    }
    // Paksa koneksi DB SQLite Laravel membaca ke arah /tmp
    putenv("DB_DATABASE={$sqlitePath}");
    $_ENV['DB_DATABASE'] = $sqlitePath;
    $_SERVER['DB_DATABASE'] = $sqlitePath;
}
// -----------------------------------------

// Proses Request seperti biasa
$response = $app->handle(Request::capture());

$response->send();

$app->terminate(Request::capture(), $response);