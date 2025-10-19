<?php

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
