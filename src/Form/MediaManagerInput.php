<?php

namespace TomatoPHP\FilamentMediaManager\Form;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use TomatoPHP\FilamentMediaManager\Models\Media;

class MediaManagerInput extends Repeater
{
    protected array $form = [];

    protected string | Closure | null $diskName = null;

    protected string | Closure | null $folderTitleFieldName = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saveRelationshipsUsing(static function (Repeater $component, HasMedia $record): void {
            $mediaComponent = $component->childComponents[0] ?? null;
            $setState = $component->getState();
            $collectMediaIds = [];
            foreach ($setState as $getMediaItems) {
                $collectMediaIds[] = array_keys($getMediaItems['file']);
            }
            $getState = [];
            $record->media()->where('collection_name', $component->name)->whereNotIn('uuid', $collectMediaIds)->delete();

            $counter = 0;
            foreach ($setState as $item) {
                $state = array_filter(array_map(function (TemporaryUploadedFile | string $file) use ($mediaComponent, $record, $component, $item, $counter) {
                    if (! $file instanceof TemporaryUploadedFile) {
                        $media = SpatieMedia::whereUuid($file)->first();
                        $customProperties = collect($item)->filter(fn ($value, $key) => $key !== 'file')->toArray();
                        foreach ($customProperties as $key => $property) {
                            $media->setCustomProperty($key, $property);
                        }
                        $media->save();

                        return $file;
                    }

                    if (! method_exists($record, 'addMediaFromString')) {
                        return $file;
                    }

                    try {
                        if (! $file->exists()) {
                            return null;
                        }
                    } catch (UnableToCheckFileExistence $exception) {
                        return null;
                    }

                    /** @var FileAdder $mediaAdder */
                    $mediaAdder = $record->addMediaFromString($file->get());

                    $filename = $mediaComponent->shouldPreserveFilenames() ? $file->getClientOriginalName() : (Str::ulid() . '.' . $file->getClientOriginalExtension());

                    $media = $mediaAdder
                        ->addCustomHeaders($mediaComponent->getCustomHeaders())
                        ->usingFileName($filename)
                        ->usingName($mediaComponent->getMediaName($file) ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        ->storingConversionsOnDisk($mediaComponent->getConversionsDisk() ?? '')
                        ->withCustomProperties(collect($item)->filter(fn ($value, $key) => $key !== 'file')->toArray())
                        ->withManipulations($mediaComponent->getManipulations())
                        ->withResponsiveImagesIf($mediaComponent->hasResponsiveImages())
                        ->withProperties($mediaComponent->getProperties())
                        ->setOrder($counter)
                        ->toMediaCollection($component->name ?? 'default', $component->getDiskName());

                    $homeFolder = config('filament-media-manager.model.folder')::where('model_type', get_class($record))
                        ->where('model_id', null)
                        ->where('collection', null)
                        ->first();
                    if (! $homeFolder) {
                        $data = [
                            'model_type' => get_class($record),
                            'model_id' => null,
                            'name' => Str::of(get_class($record))->afterLast('\\')->title()->toString(),
                        ];
                        if (filament('filament-media-manager')->allowUserAccess) {
                            $data['user_id'] = auth()->user()->id;
                            $data['user_type'] = get_class(auth()->user());
                        }
                        $homeFolder = config('filament-media-manager.model.folder')::create($data);
                    }

                    $collectionFolder = config('filament-media-manager.model.folder')::where('model_type', get_class($record))
                        ->where('model_id', null)
                        ->where('collection', $component->name)
                        ->first();
                    if (! $collectionFolder) {
                        $data = [
                            'collection' => $component->name,
                            'model_type' => get_class($record),
                            'name' => Str::of($component->name)->title()->toString(),
                        ];
                        if (filament('filament-media-manager')->allowUserAccess) {
                            $data['user_id'] = auth()->user()->id;
                            $data['user_type'] = get_class(auth()->user());
                        }
                        $collectionFolder = config('filament-media-manager.model.folder')::create($data);
                    }

                    $folder = config('filament-media-manager.model.folder')::where('collection', $component->name)
                        ->where('model_type', get_class($record))
                        ->where('model_id', $record->id)
                        ->first();

                    if (! $folder) {
                        $data = [
                            'collection' => $component->name,
                            'model_type' => get_class($record),
                            'model_id' => $record->id,
                            'name' => $component->folderTitleFieldName ? $record->{$component->folderTitleFieldName} : Str::of(get_class($record))->afterLast('\\')->title()->toString() . '[' . $record->id . ']',
                        ];

                        if (filament('filament-media-manager')->allowUserAccess) {
                            $data['user_id'] = auth()->user()->id;
                            $data['user_type'] = get_class(auth()->user());
                        }

                        $folder = config('filament-media-manager.model.folder')::create($data);
                    }

                    $callback = $media->getAttributeValue('uuid');

                    if (! $callback) {
                        $file->delete();

                        return $file;
                    }

                    $storedFile = $mediaComponent->evaluate($callback, [
                        'file' => $file,
                    ]);

                    if ($storedFile === null) {
                        return null;
                    }

                    $mediaComponent->storeFileName($storedFile, $file->getClientOriginalName());

                    $file->delete();

                    return $storedFile;
                }, Arr::wrap($item['file'])));
                $item['file'] = $state;

                $getState[] = array_merge([
                    'file' => array_keys($state)[0],
                ], collect($item)->filter(fn ($value, $key) => $key !== 'file')->toArray());

                $counter++;

            }

            $component->state($getState);

        });

        $this->reorderAction(static function (Action $action): void {
            $action->action(function (array $arguments, Repeater $component): void {
                $items = [
                    ...array_flip($arguments['items']),
                    ...$component->getState(),
                ];
                $counter = 0;
                foreach ($items as $item) {
                    $media = config('filament-media-manager.model.media')::where('uuid', array_keys($item['file'])[0])->first();
                    if ($media) {
                        $media->update([
                            'order_column' => $counter,
                        ]);
                    }
                    $counter++;
                }

                $component->state($items);

                $component->callAfterStateUpdated();
            });
        });

        $this->deleteAction(static function (Action $action): void {
            $action
                ->requiresConfirmation()
                ->action(function (array $arguments, Repeater $component) {
                    $items = $component->getState();
                    $media = config('filament-media-manager.model.media')::where('uuid', $items[$arguments['item']])->first();
                    if ($media) {
                        $media->delete();
                    }

                    unset($items[$arguments['item']]);

                    $component->state($items);

                    $component->callAfterStateUpdated();
                });
        });

        $this->loadStateFromRelationshipsUsing(static function (Repeater $component, HasMedia $record): void {
            $mediaComponent = $component->childComponents[0] ?? null;
            /** @var Model&HasMedia $record */
            $media = $record->load('media')->getMedia($component->name ?? 'default');

            $state = [];
            foreach ($media as $item) {
                $url = null;

                if ($mediaComponent->getVisibility() === 'private') {
                    $conversion = $mediaComponent->getConversion();

                    try {
                        $url = $item?->getTemporaryUrl(
                            now()->addMinutes(5),
                            (filled($conversion) && $item->hasGeneratedConversion($conversion)) ? $conversion : '',
                        );
                    } catch (Throwable $exception) {
                        // This driver does not support creating temporary URLs.
                    }
                }

                if ($mediaComponent->getConversion() && $item?->hasGeneratedConversion($mediaComponent->getConversion())) {
                    $url ??= $item->getUrl($mediaComponent->getConversion());
                }

                $url ??= $item?->getUrl();

                $state[] = array_merge([
                    'file' => $item->uuid,
                ], $item->custom_properties);
            }
            $component->state($state);
        });
    }

    /**
     * @param  array<Component> | Closure  $components
     */
    public function schema(array | Closure $components): static
    {
        $this->childComponents(array_merge([
            FileInput::make('file')
                ->disk($this->diskName)
                ->required()
                ->storeFiles(false)
                ->collection($this->name),
        ], $components));

        return $this;
    }

    public function getDiskName(): string
    {
        if ($diskName = $this->evaluate($this->diskName)) {
            return $diskName;
        }

        /** @var Model&HasMedia $model */
        $model = $this->getModelInstance();

        $collection = $this->name ?? 'default';

        /** @phpstan-ignore-next-line */
        $diskNameFromRegisteredConversions = $model
            ->getRegisteredMediaCollections()
            ->filter(fn (MediaCollection $mediaCollection): bool => $mediaCollection->name === $collection)
            ->first()
            ?->diskName;

        return $diskNameFromRegisteredConversions ?? config('filament.default_filesystem_disk');
    }

    public function disk(string | Closure | null $name): static
    {
        $this->diskName = $name;

        return $this;
    }

    public function folderTitleFieldName(string | Closure | null $folderTitleFieldName): static
    {
        $this->folderTitleFieldName = $folderTitleFieldName;

        return $this;
    }
}
