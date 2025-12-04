<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../src/routes/web.php',
        api: __DIR__.'/../src/routes/api.php',
        commands: __DIR__.'/../src/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        $middleware->alias([
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create()
    ->useAppPath(__DIR__ . '/../src/app')
    ->useConfigPath(__DIR__ . '/../src/config')
    ->useDatabasePath(__DIR__ . '/../src/database');