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

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
