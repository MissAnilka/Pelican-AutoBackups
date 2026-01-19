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
        $id = str($panel->getId())->title();

        // Only register on server panel
        if ($panel->getId() === 'server') {
            $panel->discoverWidgets(
                plugin_path($this->getId(), "src/Filament/$id/Widgets"),
                "PelicanAutoBackups\\Filament\\$id\\Widgets"
            );
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
