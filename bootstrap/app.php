<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
        ]);
        // Mark authenticated users as online for a short TTL to display
        // online/offline status in the Admin -> Users table.
        $middleware->appendToGroup('web', [\App\Http\Middleware\MarkUserOnline::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
