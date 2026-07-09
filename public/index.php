<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (isset($_SERVER['LAMBDA_TASK_ROOT'])) {
    config(['app.configuration_cached' => false]);
    config(['app.routes_cached' => false]);
    config(['app.services_cached' => false]);
    config(['app.events_cached' => false]);
}

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
} else {
    require __DIR__.'/vendor/autoload.php';
}

if (file_exists(__DIR__.'/../bootstrap/app.php')) {
    $app = require_once __DIR__.'/../bootstrap/app.php';
} else {
    $app = require_once __DIR__.'/bootstrap/app.php';
}

$response = $app->handle(Request::capture());

$response->send();

$app->terminate(Request::capture(), $response);