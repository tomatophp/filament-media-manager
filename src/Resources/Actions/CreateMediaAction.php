<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
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
                    ->multiple()
                    ->live()
                    ->maxSize('100000')
                    ->columnSpanFull()
                    ->required()
                    ->storeFiles(false),
                // A title and a description belong to a single medium; for several, they are added later by editing each
                Forms\Components\TextInput::make('title')
                    ->label(trans('filament-media-manager::messages.media.actions.create.form.title'))
                    ->visible(fn (Get $get): bool => count(Arr::wrap($get('file'))) <= 1)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(trans('filament-media-manager::messages.media.actions.create.form.description'))
                    ->visible(fn (Get $get): bool => count(Arr::wrap($get('file'))) <= 1)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) use ($folder_id) {
                $folder = Folder::find($folder_id);
                if ($folder) {
                    $files = array_values(Arr::wrap($data['file']));
                    $customProperties = [
                        'title' => count($files) === 1 ? ($data['title'] ?? null) : null,
                        'description' => count($files) === 1 ? ($data['description'] ?? null) : null,
                    ];

                    foreach ($files as $file) {
                        ($folder->model ?? $folder)
                            ->addMedia($file)
                            ->withCustomProperties($customProperties)
                            ->toMediaCollection($folder->collection);
                    }
                }

                Notification::make()->title(trans('filament-media-manager::messages.media.notifications.create-media'))->send();
            });
    }
}
