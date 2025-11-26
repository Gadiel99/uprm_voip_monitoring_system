<?php

// Rutas/definiciones para tareas de consola (scheduler y comandos Artisan personalizados).
// - Programación ETL cada 5 minutos con protección "withoutOverlapping" y logs en éxito/falla.
// - Comando "inspire" de ejemplo para imprimir una cita inspiradora.
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use App\Models\AlertSettings;

// ETL is now handled exclusively by auto-import-voip-cron.sh to prevent duplicate runs
// The cron script runs every 5 minutes and handles: download → extract → ETL → notifications
// Schedule::command('etl:run --since="5 minutes ago"')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->onFailure(function () {
//        Log::error('ETL command failed');
//     })
//     ->onSuccess(function () {
//        Log::info('ETL command completed successfully');
//     });

// NOTE: Notification check is now handled by auto-import-voip-cron.sh
// which runs after ETL completes to ensure fresh data is checked
// Removed duplicate scheduler entry to prevent double emails


// Rotate device activity data daily at midnight (maintains 2-day rolling window)
Schedule::command('activity:rotate')
    ->dailyAt('00:01') // Run at 12:01 AM every day
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('Activity data rotation failed');
    })
    ->onSuccess(function () {
       Log::info('Activity data rotation completed successfully');
    });

// Create database backup every Sunday at 3:00 AM
Schedule::command('backup:database')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('Weekly database backup failed');
    })
    ->onSuccess(function () {
       Log::info('Weekly database backup completed successfully');
    });

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
