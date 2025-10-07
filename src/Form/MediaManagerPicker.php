<?php

namespace TomatoPHP\FilamentMediaManager\Form;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentMediaManager\Models\Media;

class MediaManagerPicker extends Field
{
    protected string $view = 'filament-media-manager::forms.media-manager-picker';

    protected string | Closure | null $diskName = null;

    protected bool | Closure $isMultiple = true;

    protected string | Closure | null $collectionName = null;

    protected int | Closure | null $maxItems = null;

    protected int | Closure | null $minItems = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (MediaManagerPicker $component): Action => $component->getBrowseAction(),
            fn (MediaManagerPicker $component): Action => $component->getRemoveAction(),
            fn (MediaManagerPicker $component): Action => $component->getRemoveMediaItemAction(),
        ]);

        $this->afterStateHydrated(static function (MediaManagerPicker $component, ?Model $record): void {
            if (! $record || ! $record->exists) {
                return;
            }

            // Load media from media_has_models pivot table
            $mediaIds = \DB::table('media_has_models')
                ->where('model_type', get_class($record))
                ->where('model_id', $record->id)
                ->pluck('media_id')
                ->toArray();

            if (empty($mediaIds)) {
                $component->state($component->isMultiple() ? [] : null);

                return;
            }

            $media = Media::whereIn('id', $mediaIds)->get();

            if ($component->isMultiple()) {
                $component->state($media->pluck('uuid')->toArray());
            } else {
                $component->state($media->first()?->uuid);
            }
        });

        $this->dehydrated(false);

        $this->saveRelationshipsUsing(static function (MediaManagerPicker $component, ?Model $record) {
            if (! $record || ! $record->exists) {
                return;
            }

            $state = $component->getState();

            // Clear existing attachments
            \DB::table('media_has_models')
                ->where('model_type', get_class($record))
                ->where('model_id', $record->id)
                ->delete();

            if (empty($state)) {
                return;
            }

            // Get media by UUIDs
            $uuids = is_array($state) ? $state : [$state];
            $media = Media::whereIn('uuid', $uuids)->get();

            // Create new attachments
            foreach ($media as $mediaItem) {
                \DB::table('media_has_models')->insert([
                    'model_type' => get_class($record),
                    'model_id' => $record->id,
                    'media_id' => $mediaItem->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function getBrowseAction(): Action
    {
        return Action::make('getBrowseAction')
            ->label(trans('filament-media-manager::messages.picker.browse'))
            ->icon('heroicon-o-folder-open')
            ->modalContent(fn (MediaManagerPicker $component) => view('filament-media-manager::components.media-picker-modal', [
                'pickerKey' => $component->getId(),
                'isMultiple' => $component->isMultiple(),
                'collectionName' => $component->getCollectionName(),
                'maxItems' => $component->getMaxItems(),
                'minItems' => $component->getMinItems(),
                'currentState' => $component->getState(),
            ]))
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Close'))
            ->closeModalByClickingAway(false);
    }

    public function getRemoveAction(): Action
    {
        return Action::make('getRemoveAction')
            ->label(trans('filament-media-manager::messages.picker.remove'))
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (MediaManagerPicker $component) {
                $component->state($component->isMultiple() ? [] : null);
            });
    }

    public function getRemoveMediaItemAction(): Action
    {
        return Action::make('removeMediaItem')
            ->label(trans('filament-media-manager::messages.picker.remove'))
            ->icon('heroicon-m-x-mark')
            ->color('danger')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading(trans('filament-media-manager::messages.picker.confirm_remove'))
            ->modalDescription(trans('filament-media-manager::messages.picker.confirm_remove_message'))
            ->modalSubmitActionLabel(trans('filament-media-manager::messages.picker.remove'))
            ->action(function (MediaManagerPicker $component, array $arguments) {
                $uuid = $arguments['uuid'] ?? null;
                if (! $uuid) {
                    return;
                }

                $currentState = $component->getState();
                if ($component->isMultiple() && is_array($currentState)) {
                    $component->state(array_values(array_diff($currentState, [$uuid])));
                } elseif (! $component->isMultiple() && $currentState === $uuid) {
                    $component->state(null);
                }
            });
    }

    public function multiple(bool | Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function single(bool | Closure $condition = true): static
    {
        $this->isMultiple = ! $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->isMultiple);
    }

    public function maxItems(int | Closure | null $count): static
    {
        $this->maxItems = $count;

        return $this;
    }

    public function getMaxItems(): ?int
    {
        return $this->evaluate($this->maxItems);
    }

    public function minItems(int | Closure | null $count): static
    {
        $this->minItems = $count;

        return $this;
    }

    public function getMinItems(): ?int
    {
        return $this->evaluate($this->minItems);
    }

    public function collection(string | Closure | null $name): static
    {
        $this->collectionName = $name;

        return $this;
    }

    public function getCollectionName(): ?string
    {
        return $this->evaluate($this->collectionName);
    }

    public function disk(string | Closure | null $name): static
    {
        $this->diskName = $name;

        return $this;
    }

    public function getDiskName(): ?string
    {
        return $this->evaluate($this->diskName);
    }
}
