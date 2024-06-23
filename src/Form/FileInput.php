<?php

namespace TomatoPHP\FilamentMediaManager\Form;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileInput extends SpatieMediaLibraryFileUpload
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadStateFromRelationshipsUsing(static function (FileInput $component, HasMedia $record): void {
            /** @var Model&HasMedia $record */
            $media = $record->media()->where('uuid', $component->getState())->get()
                ->when(
                    $component->hasMediaFilter(),
                    fn (Collection $media) => $component->filterMedia($media)
                )
                ->mapWithKeys(function (Media $media): array {
                    $uuid = $media->getAttributeValue('uuid');

                    return [$uuid => $uuid];
                })
                ->toArray();

            $component->state($media);
        });

        $this->afterStateHydrated(static function (BaseFileUpload $component, string | array | null $state): void {

        });


        $this->saveRelationshipsUsing(static function (SpatieMediaLibraryFileUpload $component) {

        });

        $this->reorderUploadedFilesUsing(static function (SpatieMediaLibraryFileUpload $component, ?Model $record, array $state): array {

        });
    }
}
