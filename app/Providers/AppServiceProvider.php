<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('admin', function () {
            if (!auth()->check()) return false;
            $role = strtolower(str_replace('_', '', auth()->user()->role));
            return in_array($role, ['admin', 'superadmin']);
        });
    }
}
