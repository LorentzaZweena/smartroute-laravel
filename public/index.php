<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
if (isset($_SERVER['VERCEL_URL']) || isset($_ENV['VERCEL_URL'])) {
    $tmpCacheDir = '/tmp/storage/bootstrap/cache';
    if (!file_exists($tmpCacheDir)) {
        mkdir($tmpCacheDir, 0755, true);
    }
    
    putenv("APP_PACKAGES_CACHE={$tmpCacheDir}/packages.php");
    $_ENV['APP_PACKAGES_CACHE'] = "{$tmpCacheDir}/packages.php";
    $_SERVER['APP_PACKAGES_CACHE'] = "{$tmpCacheDir}/packages.php";
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handle(Request::capture());

$response->send();

$app->terminate(Request::capture(), $response);