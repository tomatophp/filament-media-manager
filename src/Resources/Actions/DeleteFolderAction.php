<?php

namespace TomatoPHP\FilamentMediaManager\Resources\Actions;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;

class DeleteFolderAction
{
    public static function make(int $folder_id): Actions\Action
    {
        return Actions\Action::make('delete_folder')
            ->mountUsing(function () use ($folder_id){
                session()->put('folder_id', $folder_id);
            })
            ->hiddenLabel()
            ->requiresConfirmation()
            ->tooltip(trans('filament-media-manager::messages.media.actions.delete.label'))
            ->label(trans('filament-media-manager::messages.media.actions.delete.label'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->action(function () use ($folder_id){
                $folder = config('filament-media-manager.model.folder')::find($folder_id);
                $folder->delete();
                session()->forget('folder_id');

                Notification::make()->title(trans('filament-media-manager::messages.media.notificaitons.delete-folder'))->send();
                return redirect()->route('filament.'.filament()->getCurrentPanel()->getId().'.resources.folders.index');
            });
    }
}
