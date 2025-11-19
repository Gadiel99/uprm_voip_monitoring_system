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
        // CacheManager ensures logout is effective and back button won't show cached pages
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\MarkUserOnline::class,
            \App\Http\Middleware\CacheManager::class,
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
        
        // Handle 419 Page Expired (CSRF token expired) - redirect to login or home
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                if (auth()->check()) {
                    return redirect()->route('dashboard')->with('error', 'Your session has expired. Please try again.');
                }
                return redirect()->route('login')->with('status', 'Your session has expired. Please login again.');
            }
        });
    })->create();
