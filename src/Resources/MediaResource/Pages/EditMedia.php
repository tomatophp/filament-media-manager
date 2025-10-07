<?php

namespace TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
