<?php

namespace App\Providers;

use App\Filesystem\SafeFilesystem;
use App\Models\BusinessNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', function () {
            return new SafeFilesystem;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('notification', fn (string $value) => BusinessNotification::findOrFail($value));
    }
}
