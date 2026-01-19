<?php

namespace PelicanAutoBackups\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use PelicanAutoBackups\Console\Commands\ProcessAutoBackupsCommand;

class AutoBackupsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            plugin_path('pelican-autobackups', 'config/autobackups.php'),
            'pelican-autobackups'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessAutoBackupsCommand::class,
            ]);
        }

        // Schedule the auto backup command to run every minute
        // The command itself will check if backups need to be created
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('p:autobackups:process')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
