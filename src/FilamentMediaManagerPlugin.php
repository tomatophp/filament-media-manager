<?php

namespace TomatoPHP\FilamentMediaManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentArtisan\Pages\Artisan;


class FilamentMediaManagerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-media-manager';
    }

    public function register(Panel $panel): void
    {
        $panel;

    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
