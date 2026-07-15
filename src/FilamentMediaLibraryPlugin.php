<?php

namespace Immera\FilamentMediaLibrary;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Immera\FilamentMediaLibrary\Pages\MediaLibrary;

class FilamentMediaLibraryPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-media-library';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            MediaLibrary::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
