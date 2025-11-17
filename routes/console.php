<?php

// Rutas/definiciones para tareas de consola (scheduler y comandos Artisan personalizados).
// - Programación ETL cada 5 minutos con protección "withoutOverlapping" y logs en éxito/falla.
// - Comando "inspire" de ejemplo para imprimir una cita inspiradora.
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use App\Models\AlertSettings;

Schedule::command('etl:run --since="5 minutes ago"')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('ETL command failed');
    })
    ->onSuccess(function () {
       Log::info('ETL command completed successfully');
    });

// Check and send notifications for critical conditions every 5 minutes
Schedule::command('notifications:check')
   ->everyFiveMinutes()
   ->when(function () {
      try {
         $settings = AlertSettings::current();
         return ($settings->is_active ?? true) && ($settings->email_notifications_enabled ?? true);
      } catch (\Throwable $e) {
         // If settings cannot be read, do not block notifications by default
         Log::warning('Scheduler could not read AlertSettings: '.$e->getMessage());
         return true;
      }
   })
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

// Rotate device activity data daily at midnight
Schedule::command('activity:rotate')
    ->dailyAt('00:01') // Run at 12:01 AM
    ->withoutOverlapping()
    ->onFailure(function () {
       Log::error('Activity data rotation failed');
    })
    ->onSuccess(function () {
       Log::info('Activity data rotation completed successfully');
    });

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
