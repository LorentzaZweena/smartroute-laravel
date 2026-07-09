<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
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
}

$response = $app->handle(Request::capture());

$response->send();

$app->terminate(Request::capture(), $response);