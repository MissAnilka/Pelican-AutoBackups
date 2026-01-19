<?php

namespace PelicanAutoBackups;

use Filament\Contracts\Plugin;
use Filament\Panel;

class AutoBackupsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pelican-autobackups';
    }

    public function register(Panel $panel): void
    {
        // Use discoverWidgets to properly register widgets for each panel
        $id = str($panel->getId())->title();
        
        $panel->discoverWidgets(
            plugin_path($this->getId(), "src/Filament/{$id}/Widgets"),
            "PelicanAutoBackups\\Filament\\{$id}\\Widgets"
        );
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
