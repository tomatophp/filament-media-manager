<?php

use Filament\Facades\Filament;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;

it('registers plugin', function () {
    $panel = Filament::getCurrentPanel();

    $panel->plugins([
        FilamentMediaManagerPlugin::make(),
    ]);

    expect($panel->getPlugin('filament-media-manager'))
        ->not()
        ->toThrow(Exception::class);
});
