<?php

namespace PelicanAutoBackups\Providers;

use App\Enums\HeaderWidgetPosition;
use App\Filament\Server\Resources\Backups\Pages\ListBackups;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use PelicanAutoBackups\Filament\Server\Widgets\AutoBackupSettingsWidget;
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

        // Register the widget on the backups list page
        ListBackups::registerCustomHeaderWidgets(HeaderWidgetPosition::Before, AutoBackupSettingsWidget::class);
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
