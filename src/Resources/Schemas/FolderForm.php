<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use TomatoPHP\FilamentIcons\Components\IconPicker;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;

/**
 * The fields of a folder, shared by the folders resource, the sub folder action and the media picker.
 */
class FolderForm
{
    /**
     * @return array<Component | Forms\Components\Field>
     */
    public static function components(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label(trans('filament-media-manager::messages.folders.columns.name'))
                ->columnSpanFull()
                ->lazy()
                ->afterStateUpdated(function (Set $set, Get $get) {
                    $set('collection', Str::slug($get('name')));
                })
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('collection')
                ->label(trans('filament-media-manager::messages.folders.columns.collection'))
                ->columnSpanFull()
                ->unique(config('filament-media-manager.model.folder'), ignoreRecord: true)
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label(trans('filament-media-manager::messages.folders.columns.description'))
                ->columnSpanFull()
                ->maxLength(255),
            IconPicker::make('icon')
                ->label(trans('filament-media-manager::messages.folders.columns.icon')),
            ...static::colorComponents(),
            Forms\Components\Toggle::make('is_protected')
                ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                ->live()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('password')
                ->label(trans('filament-media-manager::messages.folders.columns.password'))
                ->hidden(fn (Get $get) => ! $get('is_protected'))
                ->password()
                ->revealable()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('password_confirmation')
                ->label(trans('filament-media-manager::messages.folders.columns.password_confirmation'))
                ->hidden(fn (Get $get) => ! $get('is_protected'))
                ->password()
                ->required()
                ->revealable()
                ->maxLength(255),
        ];
    }

    /**
     * The color picker, and the plugin's folderColors() as swatches above it. Picking a swatch sets the color,
     * and a color from the palette marks its swatch.
     *
     * @return array<Component | Forms\Components\Field>
     */
    public static function colorComponents(): array
    {
        $palette = FilamentMediaManagerPlugin::resolveFolderColors();

        return [
            Forms\Components\ToggleButtons::make('color_palette')
                ->label(trans('filament-media-manager::messages.folders.columns.color'))
                ->options(collect($palette)->mapWithKeys(fn (string $color): array => [
                    $color => new HtmlString(
                        '<span style="display: inline-block; width: 1rem; height: 1rem; border-radius: 9999px; background-color: ' . e($color) . '"></span>'
                        . '<span class="sr-only">' . e($color) . '</span>'
                    ),
                ])->all())
                ->inline()
                ->visible(filled($palette))
                ->dehydrated(false)
                ->live()
                ->afterStateHydrated(function (Forms\Components\ToggleButtons $component, Get $get) use ($palette) {
                    $component->state(in_array($get('color'), $palette, true) ? $get('color') : null);
                })
                ->afterStateUpdated(function (?string $state, Set $set) {
                    if (filled($state)) {
                        $set('color', $state);
                    }
                })
                ->columnSpanFull(),
            Forms\Components\ColorPicker::make('color')
                ->label(trans(filled($palette)
                    ? 'filament-media-manager::messages.folders.columns.custom_color'
                    : 'filament-media-manager::messages.folders.columns.color'))
                ->default(FilamentMediaManagerPlugin::resolveDefaultFolderColor())
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set) use ($palette) {
                    $set('color_palette', in_array($state, $palette, true) ? $state : null);
                }),
        ];
    }
}
