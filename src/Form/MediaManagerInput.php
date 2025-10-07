<?php

namespace TomatoPHP\FilamentMediaManager\Form;

use Closure;
use Filament\Actions\Action;
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
use Throwable;
use TomatoPHP\FilamentMediaManager\Models\Media;

class MediaManagerInput extends Repeater
{
    protected array $form = [];

    protected string | Closure | null $diskName = null;

    protected string | Closure | null $folderTitleFieldName = null;

    protected bool $isSingle = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure repeater based on single mode
        if ($this->isSingle) {
            $this->minItems(1);
            $this->maxItems(1);
            $this->defaultItems(1);
            $this->reorderable(false);
            $this->addActionLabel(trans('filament-media-manager::messages.media.actions.create.label'));
        }

        $this->saveRelationshipsUsing(static function (Repeater $component, HasMedia $record): void {
            $childComponents = $component->getChildComponents();
            /** @var FileInput|null $mediaComponent */
            $mediaComponent = ! empty($childComponents) ? $childComponents[0] : null;
            $setState = $component->getState();

            if (! $mediaComponent) {
                return;
            }

            // If state is null, empty, or not an array, nothing to process
            if ($setState === null || ! is_array($setState) || empty($setState)) {
                return;
            }

            // Collect existing media UUIDs (only strings, not TemporaryUploadedFile objects)
            $collectMediaIds = [];
            foreach ($setState as $getMediaItems) {
                if (isset($getMediaItems['file'])) {
                    $file = $getMediaItems['file'];

                    // Handle array of files
                    if (is_array($file)) {
                        foreach ($file as $key => $value) {
                            // If it's a UUID string in array keys or values, collect it
                            if (is_string($key) && Str::isUuid($key)) {
                                $collectMediaIds[] = $key;
                            } elseif (is_string($value) && Str::isUuid($value)) {
                                $collectMediaIds[] = $value;
                            }
                        }
                    }
                    // Handle single UUID string (existing media)
                    elseif (is_string($file) && Str::isUuid($file)) {
                        $collectMediaIds[] = $file;
                    }
                    // TemporaryUploadedFile objects are new uploads, not existing media
                }
            }
            $getState = [];

            // Only delete media that's been removed (not in the current state)
            if (! empty($collectMediaIds)) {
                $record->media()->where('collection_name', $component->name)->whereNotIn('uuid', $collectMediaIds)->delete();
            }

            $counter = 0;
            foreach ($setState as $item) {
                // Skip if no file data
                if (! isset($item['file'])) {
                    continue;
                }

                $state = array_filter(array_map(function (TemporaryUploadedFile | string $file) use ($mediaComponent, $record, $component, $item, &$counter) {
                    if (! $file instanceof TemporaryUploadedFile) {
                        $media = SpatieMedia::whereUuid($file)->first();

                        if ($media) {
                            $customProperties = collect($item)->filter(fn ($value, $key) => $key !== 'file')->toArray();
                            foreach ($customProperties as $key => $property) {
                                $media->setCustomProperty($key, $property);
                            }
                            $media->order_column = $counter;
                            $media->save();
                            $counter++;
                        }

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

                    $file->delete();

                    // Increment counter for next media
                    $counter++;

                    // Return the media UUID
                    return $media->uuid;
                }, Arr::wrap($item['file'])));

                // Process each UUID returned from the upload
                foreach ($state as $uuid) {
                    if ($uuid) {
                        $getState[] = array_merge([
                            'file' => $uuid,
                        ], collect($item)->filter(fn ($value, $key) => $key !== 'file')->toArray());
                    }
                }
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
                    if (is_array($item) && isset($item['file']) && is_array($item['file']) && ! empty($item['file'])) {
                        $media = Media::where('uuid', array_keys($item['file'])[0])->first();
                        if ($media) {
                            $media->update([
                                'order_column' => $counter,
                            ]);
                        }
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
                    $media = Media::where('uuid', $items[$arguments['item']])->first();
                    if ($media) {
                        $media->delete();
                    }

                    unset($items[$arguments['item']]);

                    $component->state($items);

                    $component->callAfterStateUpdated();
                });
        });

        $this->loadStateFromRelationshipsUsing(static function (Repeater $component, HasMedia $record): void {
            $childComponents = $component->getChildComponents();
            /** @var FileInput|null $mediaComponent */
            $mediaComponent = ! empty($childComponents) ? $childComponents[0] : null;
            /** @var Model&HasMedia $record */
            $media = $record->load('media')->getMedia($component->name ?? 'default');

            if (! $mediaComponent) {
                return;
            }

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

    public function single(bool $condition = true): static
    {
        $this->isSingle = $condition;

        if ($condition) {
            $this->minItems(1);
            $this->maxItems(1);
            $this->defaultItems(1);
            $this->reorderable(false);
            $this->addActionLabel(trans('filament-media-manager::messages.media.actions.create.label'));
        }

        return $this;
    }
}
