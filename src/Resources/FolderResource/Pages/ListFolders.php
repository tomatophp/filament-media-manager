<?php

namespace TomatoPHP\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;

class ListFolders extends ManageRecords
{
    protected static string $resource = FolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        session()->forget('folder_id');
        session()->forget('folder_password');
    }
}
