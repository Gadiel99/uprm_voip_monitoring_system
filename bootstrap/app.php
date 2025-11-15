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
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\MarkUserOnline::class,
            // LogPageAccess removed - only logging important actions now
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log database connection errors
        $exceptions->report(function (\Illuminate\Database\QueryException $e) {
            \App\Helpers\SystemLogger::logError(
                'Database Error: ' . $e->getMessage(),
                [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'code' => $e->getCode()
                ]
            );
        });
        
        // Log authentication errors
        $exceptions->report(function (\Illuminate\Auth\AuthenticationException $e) {
            \App\Helpers\SystemLogger::logError(
                'Authentication Error: ' . $e->getMessage(),
                ['guards' => $e->guards()]
            );
        });
        
        // Log validation errors (like wrong password)
        $exceptions->report(function (\Illuminate\Validation\ValidationException $e) {
            if (request()->is('login')) {
                \App\Helpers\SystemLogger::logError(
                    'Login Validation Failed',
                    [
                        'email' => request()->input('email'),
                        'errors' => $e->errors()
                    ]
                );
            }
        });
        
        // Log general exceptions
        $exceptions->report(function (\Throwable $e) {
            // Only log serious errors (not 404s or validation errors already handled)
            if (!($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) &&
                !($e instanceof \Illuminate\Validation\ValidationException)) {
                \App\Helpers\SystemLogger::logError(
                    'System Error: ' . $e->getMessage(),
                    [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                );
            }
        });
    })->create();
