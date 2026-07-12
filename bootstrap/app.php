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
use Illuminate\Support\Facades\URL;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        if (isset($_SERVER['VERCEL_ENV']) || env('APP_ENV') === 'production') {
            $middleware->alias([
                'force_https' => function ($request, $next) {
                    if (!$request->secure() && env('APP_ENV') === 'production') {
                        return redirect()->secure($request->getRequestUri());
                    }
                    URL::forceScheme('https');
                    return $next($request);
                },
            ]);
            $middleware->append('force_https');
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if (isset($storagePath) && is_dir($storagePath)) {
    $app->useStoragePath($storagePath);
}

return $app;