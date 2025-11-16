<?php

// Rutas/definiciones para tareas de consola (scheduler y comandos Artisan personalizados).
// - Programación ETL cada 5 minutos con protección "withoutOverlapping" y logs en éxito/falla.
// - Comando "inspire" de ejemplo para imprimir una cita inspiradora.
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::command('etl:run --since="5 minutes ago"')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('ETL command failed');
    })
    ->onSuccess(function () {
       Log::info('ETL command completed successfully');
    });

// Check and send notifications for critical conditions every 10 minutes
Schedule::command('notifications:check')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('Notification check command failed');
    })
    ->onSuccess(function () {
       Log::info('Notification check completed successfully');
    });

// Clean up old import files daily at 2:00 AM
Schedule::command('imports:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('Import file cleanup failed');
    })
    ->onSuccess(function () {
       Log::info('Import file cleanup completed successfully');
    });

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
