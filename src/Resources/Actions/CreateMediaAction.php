<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use TomatoPHP\FilamentMediaManager\Models\Folder;

class CreateMediaAction
{
    public static function make(int $folder_id): Actions\Action
    {
        return Actions\Action::make('create_media')
            ->mountUsing(function (?Schema $schema) use ($folder_id) {
                // Replaces Filament's default mount callback, so the form has to be filled here: without it,
                // `mountedActions.0.data.file` does not exist and the file upload fails to entangle
                $schema?->fill();

                session()->put('folder_id', $folder_id);
            })
            ->label(trans('filament-media-manager::messages.media.actions.create.label'))
            ->icon('heroicon-o-plus')
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->label(trans('filament-media-manager::messages.media.actions.create.form.file'))
                    ->maxSize('100000')
                    ->columnSpanFull()
                    ->required()
                    ->storeFiles(false),
                Forms\Components\TextInput::make('title')
                    ->label(trans('filament-media-manager::messages.media.actions.create.form.title'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(trans('filament-media-manager::messages.media.actions.create.form.description'))
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Folder::find($folder_id);
                if ($folder) {
                    if ($folder->model) {
                        $folder->model->addMedia($data['file'])
                            ->withCustomProperties([
                                'title' => $data['title'],
                                'description' => $data['description'],
                            ])
                            ->toMediaCollection($folder->collection);
                    } else {
                        $folder->addMedia($data['file'])
                            ->withCustomProperties([
                                'title' => $data['title'],
                                'description' => $data['description'],
                            ])
                            ->toMediaCollection($folder->collection);
                    }

                }

                Notification::make()->title(trans('filament-media-manager::messages.media.notifications.create-media'))->send();
            });
    }
}
