<?php

namespace TomatoPHP\FilamentMediaManager\Resources\MediaResource\Pages;

use TomatoPHP\FilamentMediaManager\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;
}
