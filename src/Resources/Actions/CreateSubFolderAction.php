<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;

use Filament\Actions;
use Filament\Notifications\Notification;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Resources\Schemas\FolderForm;

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
            ->schema(FolderForm::components())
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
