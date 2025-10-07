<?php

namespace TomatoPHP\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use TomatoPHP\FilamentMediaManager\Resources\FolderResource;

class EditFolder extends EditRecord
{
    protected static string $resource = FolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
