<?php

namespace PelicanAutoBackups\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use PelicanAutoBackups\Services\AutoBackupService;

class AutoBackupsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the service
        $this->app->singleton(AutoBackupService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Schedule the auto backup command to run every hour
        // The command itself will check if backups need to be created
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('p:autobackups:process')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
