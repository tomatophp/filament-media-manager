<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use TomatoPHP\FilamentIcons\Components\IconPicker;
use TomatoPHP\FilamentMediaManager\Models\Folder;

class CreateSubFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        return Actions\Action::make('create_sub_folder')
            ->hidden(fn () => ! filament('filament-media-manager')->allowSubFolders)
            ->mountUsing(function () use ($folder_id) {
                session()->put('folder_id', $folder_id);
            })
            ->color('info')
            ->hiddenLabel()
            ->tooltip(trans('filament-media-manager::messages.media.actions.sub_folder.label'))
            ->label(trans('filament-media-manager::messages.media.actions.sub_folder.label'))
            ->icon('heroicon-o-folder-minus')
            ->fillForm(fn () => [
                'name' => '',
                'collection' => '',
                'description' => '',
                'icon' => '',
                'color' => '',
                'is_protected' => false,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->schema([
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
                    ->unique(Folder::class)
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
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Folder::find($folder_id);
                if ($folder) {
                    $data['user_id'] = auth()->user()->id;
                    $data['user_type'] = get_class(auth()->user());
                    $data['model_id'] = $folder_id;
                    $data['model_type'] = Folder::class;
                    Folder::query()->create($data);
                }

                Notification::make()
                    ->title('Folder Created')
                    ->body('Folder Created Successfully')
                    ->success()
                    ->send();
            });
    }
}
