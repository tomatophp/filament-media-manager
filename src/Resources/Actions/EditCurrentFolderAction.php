<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class EditCurrentFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        $form = config('filament-media-manager.model.folder')::query()->where('id', $folder_id)->with('users')->first()?->toArray();
        $form['users'] = collect($form['users'])->pluck('id')->toArray();

        return Actions\Action::make('edit_current_folder')
            ->hiddenLabel()
            ->mountUsing(function () use ($folder_id) {
                session()->put('folder_id', $folder_id);
            })
            ->tooltip(trans('filament-media-manager::messages.media.actions.edit.label'))
            ->label(trans('filament-media-manager::messages.media.actions.edit.label'))
            ->icon('heroicon-o-pencil-square')
            ->color('warning')
            ->schema(function () {
                return [
                    Grid::make([
                        'sm' => 1,
                        'md' => 2,
                    ])
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label(trans('filament-media-manager::messages.folders.columns.name'))
                                ->columnSpanFull()
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
                                ->confirmed()
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
                            Forms\Components\Toggle::make('is_public')
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->label(trans('filament-media-manager::messages.folders.columns.is_public'))
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('has_user_access')
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->hidden(fn (Get $get) => $get('is_public'))
                                ->label(trans('filament-media-manager::messages.folders.columns.has_user_access'))
                                ->live()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('users')
                                ->required()
                                ->visible(filament('filament-media-manager')->allowUserAccess)
                                ->hidden(fn (Get $get) => ! $get('has_user_access'))
                                ->label(trans('filament-media-manager::messages.folders.columns.users'))
                                ->searchable()
                                ->multiple()
                               // Replace it  ->options(config('filament-media-manager.user.model', \App\Models\User::class)::query()->where('id', '!=', auth()->user()->id)->pluck(config('filament-media-manager.user.column_name'), 'id')->toArray()), with:
                                  ->options(function () {
                                    $userModel = config('filament-media-manager.user.model', \App\Models\User::class);
                                    $columnName = config('filament-media-manager.user.column_name', 'name');
                                    $idColumn = config('filament-media-manager.user.id_column', 'id');
                                    
                                    return $userModel::query()
                                        ->where($idColumn, '!=', auth()->id())
                                        ->pluck($columnName, $idColumn)
                                        ->toArray();
                                }),
                        ]),
                ];
            })
            ->fillForm($form)
            ->action(function (array $data) use ($folder_id) {
                $folder = config('filament-media-manager.model.folder')::find($folder_id);
                $folder->update($data);

                if (isset($data['users'])) {
                    $folder->users()->sync($data['users']);
                }

                Notification::make()
                    ->body(trans('filament-media-manager::messages.media.notifications.edit-folder'))
                    ->success()
                    ->send();
            });
    }
}
