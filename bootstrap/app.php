<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 1. BAJAK PATH CACHE UNTUK VERCEL (Tambahkan blok ini di paling atas)
if (isset($_SERVER['VERCEL_URL']) || env('LARAVEL_STORAGE_PATH')) {
    $app = new Application(
        $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
    );

    // Paksa Laravel menulis cache bootstrap dan storage ke folder /tmp yang writable
    $app->useStoragePath('/tmp/storage');
    $app->bootstrapWith([
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    ]);
    
    // Set environment variable secara runtime agar config cache aman
    $_ENV['APP_CONFIG_CACHE'] = '/tmp/storage/framework/config.php';
    $_ENV['APP_ROUTES_CACHE'] = '/tmp/storage/framework/routes.php';
    $_ENV['APP_SERVICES_CACHE'] = '/tmp/storage/framework/services.php';
    $_ENV['APP_PACKAGES_CACHE'] = '/tmp/storage/framework/packages.php';
}

// 2. CONFIG STRUKTUR BAWAAN LARAVEL (Biarkan bagian bawah ini tetap seperti aslinya)
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();