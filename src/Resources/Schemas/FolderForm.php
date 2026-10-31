<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use TomatoPHP\FilamentIcons\Components\IconPicker;

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
            Forms\Components\ColorPicker::make('color')
                ->label(trans('filament-media-manager::messages.folders.columns.color')),
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
}
