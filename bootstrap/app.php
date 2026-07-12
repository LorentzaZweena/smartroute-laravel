<?php
if (!class_exists('Pdo\Mysql')) {
    class DummyMysql { const ATTR_SSL_CA = 1014; }
    class_alias('DummyMysql', 'Pdo\Mysql');
}

if (isset($_SERVER['VERCEL_ENV']) || env('LOG_CHANNEL') === 'stderr') {
    $storagePath = '/tmp/storage';
    if (!is_dir($storagePath)) {
        @mkdir($storagePath, 0755, true);
        @mkdir($storagePath . '/logs', 0755, true);
        @mkdir($storagePath . '/framework', 0755, true);
        @mkdir($storagePath . '/framework/views', 0755, true);
        @mkdir($storagePath . '/framework/cache', 0755, true);
        @mkdir($storagePath . '/framework/sessions', 0755, true);
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
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
    })
    ->create();

if (isset($storagePath) && is_dir($storagePath)) {
    $app->useStoragePath($storagePath);
}

return $app;