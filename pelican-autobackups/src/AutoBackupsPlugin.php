<?php

namespace PelicanAutoBackups;

use Filament\Contracts\Plugin;
use Filament\Panel;
use PelicanAutoBackups\Filament\Server\Widgets\AutoBackupWidget;

class AutoBackupsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pelican-autobackups';
    }

    public function register(Panel $panel): void
    {
        // Register widgets for the server panel
        if ($panel->getId() === 'server') {
            $panel->widgets([
                AutoBackupWidget::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}
