<?php

namespace TomatoPHP\FilamentMediaManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\On;
use Livewire\Component;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use TomatoPHP\FilamentMediaManager\Models\Media;

class MediaPicker extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?int $currentFolderId = null;

    public bool $isMultiple = true;

    public array $selectedMedia = [];

    public ?string $collectionName = null;

    public string $pickerKey = '';

    public string $search = '';

    public ?int $pendingFolderId = null;

    public ?int $maxItems = null;

    public ?int $minItems = null;

    public function mount(
        string $pickerKey,
        bool $isMultiple = true,
        ?string $collectionName = null,
        ?int $maxItems = null,
        ?int $minItems = null,
        $initialState = null
    ): void {
        $this->pickerKey = $pickerKey;
        $this->isMultiple = $isMultiple;
        $this->collectionName = $collectionName;
        $this->maxItems = $maxItems;
        $this->minItems = $minItems;

        // Initialize selectedMedia with current state
        if ($initialState) {
            if ($isMultiple && is_array($initialState)) {
                $this->selectedMedia = $initialState;
            } elseif (! $isMultiple && ! is_array($initialState)) {
                $this->selectedMedia = [$initialState];
            }
        }
    }

    public function openFolder(int $folderId): void
    {
        $folder = Folder::find($folderId);

        if (! $folder) {
            return;
        }

        // Check if folder is protected
        if ($folder->is_protected) {
            $this->pendingFolderId = $folderId;
            $this->mountAction('verifyPassword', ['folder' => $folder]);

            return;
        }

        // Don't reset selections when navigating
        $this->currentFolderId = $folderId;
    }

    public function verifyPassword(): Action
    {
        return Action::make('verifyPassword')
            ->modalHeading(fn (array $arguments) => __('Enter Password for') . ' ' . $arguments['folder']->name)
            ->modalIcon('heroicon-o-lock-closed')
            ->schema([
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('new-password')
                    ->id('folder-password-' . uniqid()),
            ])
            ->action(function (array $arguments, array $data) {
                $folder = $arguments['folder'];

                if ($folder->password !== $data['password']) {
                    Notification::make()
                        ->title(__('Password is incorrect'))
                        ->danger()
                        ->send();

                    $this->pendingFolderId = null;

                    return;
                }

                // Password correct, open folder
                $this->currentFolderId = $this->pendingFolderId;
                $this->pendingFolderId = null;

                Notification::make()
                    ->title(__('Access granted'))
                    ->success()
                    ->send();
            })
            ->modalSubmitActionLabel(__('Confirm'));
    }

    public function uploadMedia(): Action
    {
        return Action::make('uploadMedia')
            ->label(__('Upload Media'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->visible(fn () => $this->currentFolderId !== null)
            ->schema([
                FileUpload::make('files')
                    ->label(__('Files'))
                    ->multiple()
                    ->required()
                    ->maxSize(10240)
                    ->storeFiles(false),
            ])
            ->action(function (array $data) {
                $folder = Folder::find($this->currentFolderId);

                if (! $folder) {
                    return;
                }

                $uploadedMedia = [];

                foreach ($data['files'] as $file) {
                    // Determine which model to attach media to
                    if ($folder->model_type && $folder->model_id) {
                        // Attach to folder's specific model instance
                        $model = app($folder->model_type)->find($folder->model_id);
                    } elseif ($folder->model) {
                        // Use folder's model if available
                        $model = $folder->model;
                    } else {
                        // Attach to folder itself for collection folders
                        $model = $folder;
                    }

                    if (! $model) {
                        continue;
                    }

                    // Pass file directly like CreateMediaAction does
                    $media = $model
                        ->addMedia($file)
                        ->toMediaCollection($folder->collection ?? 'default');

                    $uploadedMedia[] = $media->uuid;
                }

                Notification::make()
                    ->title(__('Media uploaded successfully'))
                    ->success()
                    ->send();

                // Auto-select uploaded media
                if ($this->isMultiple) {
                    foreach ($uploadedMedia as $uuid) {
                        if ($this->maxItems && count($this->selectedMedia) >= $this->maxItems) {
                            break;
                        }
                        if (! in_array($uuid, $this->selectedMedia)) {
                            $this->selectedMedia[] = $uuid;
                        }
                    }
                } else {
                    $this->selectedMedia = [$uploadedMedia[0] ?? null];
                    if ($uploadedMedia) {
                        $this->selectMedia();
                    }
                }
            });
    }

    public function goBack(): void
    {
        if ($this->currentFolderId) {
            $currentFolder = Folder::find($this->currentFolderId);

            if ($currentFolder) {
                // If in an instance folder, go back to collection folder
                if ($currentFolder->model_type && $currentFolder->model_id && $currentFolder->collection) {
                    // Find the collection folder
                    $collectionFolder = Folder::where('model_type', $currentFolder->model_type)
                        ->whereNull('model_id')
                        ->where('collection', $currentFolder->collection)
                        ->first();
                    $this->currentFolderId = $collectionFolder?->id;
                }
                // If in a collection folder, go back to model type folder
                elseif ($currentFolder->model_type && ! $currentFolder->model_id && $currentFolder->collection) {
                    // Find the model type folder
                    $modelTypeFolder = Folder::where('model_type', $currentFolder->model_type)
                        ->whereNull('model_id')
                        ->whereNull('collection')
                        ->first();
                    $this->currentFolderId = $modelTypeFolder?->id;
                }
                // Otherwise use parent_id or go to root
                else {
                    $this->currentFolderId = $currentFolder->parent_id;
                }
            } else {
                $this->currentFolderId = null;
            }
        }
        // Keep selections when going back
    }

    public function toggleMediaSelection(string $mediaUuid): void
    {
        if ($this->isMultiple) {
            if (in_array($mediaUuid, $this->selectedMedia)) {
                $this->selectedMedia = array_values(array_diff($this->selectedMedia, [$mediaUuid]));
            } else {
                // Check max items limit
                if ($this->maxItems && count($this->selectedMedia) >= $this->maxItems) {
                    Notification::make()
                        ->title(__('Maximum :count items allowed', ['count' => $this->maxItems]))
                        ->warning()
                        ->send();

                    return;
                }
                $this->selectedMedia[] = $mediaUuid;
            }
        } else {
            $this->selectedMedia = [$mediaUuid];
            // Auto-select and close for single selection
            $this->selectMedia();
        }
    }

    public function removeSelection(string $mediaUuid): void
    {
        $this->selectedMedia = array_values(array_diff($this->selectedMedia, [$mediaUuid]));
    }

    public function selectMedia(): void
    {
        if (empty($this->selectedMedia)) {
            Notification::make()
                ->title(__('No media selected'))
                ->warning()
                ->send();

            return;
        }

        // Check minimum items
        if ($this->minItems && count($this->selectedMedia) < $this->minItems) {
            Notification::make()
                ->title(__('Minimum :count items required', ['count' => $this->minItems]))
                ->warning()
                ->send();

            return;
        }

        $count = count($this->selectedMedia);
        $selectedData = $this->isMultiple ? $this->selectedMedia : $this->selectedMedia[0];

        // Show success notification
        Notification::make()
            ->title(__('Successfully selected :count item(s)', ['count' => $count]))
            ->success()
            ->send();

        // Dispatch window event using JavaScript to update form and close modal
        $this->js(
            "window.dispatchEvent(new CustomEvent('media-selected-{$this->pickerKey}', { detail: " . json_encode($selectedData) . ' }));'
        );

        // Reset state
        $this->selectedMedia = [];
        $this->currentFolderId = null;
        $this->search = '';
    }

    public function getFoldersProperty()
    {
        // Remove user access global scope to show all folders
        $query = Folder::withoutGlobalScope('user');

        if ($this->currentFolderId) {
            $currentFolder = Folder::find($this->currentFolderId);

            if ($currentFolder) {
                // If current folder has model_type but no collection, show collection folders
                if ($currentFolder->model_type && ! $currentFolder->collection) {
                    $query->where('model_type', $currentFolder->model_type)
                        ->whereNull('model_id')
                        ->whereNotNull('collection');
                }
                // If current folder has model_type and collection, show instance folders
                elseif ($currentFolder->model_type && $currentFolder->collection && ! $currentFolder->model_id) {
                    $query->where('model_type', $currentFolder->model_type)
                        ->whereNotNull('model_id')
                        ->where('collection', $currentFolder->collection);
                }
                // Otherwise show subfolders using parent_id
                else {
                    $query->where('parent_id', $this->currentFolderId);
                }
            }
        } else {
            // Root level: match FolderResource query logic exactly
            // Wrap in closure to properly group with other conditions
            $query->where(function ($q) {
                $q->where('model_id', null)
                    ->where('collection', null)
                    ->orWhere('model_type', null);
            });
        }

        if ($this->collectionName) {
            $query->where('collection', $this->collectionName);
        }

        // Apply search filter
        if (filled($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->get();
    }

    public function getMediaProperty()
    {
        // No media at root level or model type level
        if (! $this->currentFolderId) {
            if (filled($this->search) && $this->collectionName) {
                $query = Media::withoutGlobalScope('folder')
                    ->where('collection_name', $this->collectionName)
                    ->where(function ($q) {
                        $q->where('file_name', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    });

                return $query->get();
            }

            return collect();
        }

        $folder = Folder::find($this->currentFolderId);

        if (! $folder) {
            return collect();
        }

        // Remove the global scope temporarily to get media
        $query = Media::withoutGlobalScope('folder');

        // Filter media based on folder structure (same logic as Media model's global scope)
        if ($folder->collection) {
            if ($folder->model_type && $folder->model_id) {
                // Instance folder - show only media belonging to this specific model instance
                $query->where('model_type', $folder->model_type)
                    ->where('model_id', $folder->model_id)
                    ->where('collection_name', $folder->collection);
            } elseif ($folder->model_type && ! $folder->model_id) {
                // Collection folder - show media without model_id (not belonging to specific instances)
                $query->where('collection_name', $folder->collection)
                    ->whereNull('model_id');
            } else {
                // Regular folder - show all media in collection
                $query->where('collection_name', $folder->collection);
            }
        } else {
            // If no collection, don't show any media
            return collect();
        }

        // Apply search filter
        if (filled($this->search)) {
            $query->where(function ($q) {
                $q->where('file_name', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function getCurrentFolderProperty(): ?Folder
    {
        return $this->currentFolderId ? Folder::find($this->currentFolderId) : null;
    }

    public function render()
    {
        return view('filament-media-manager::livewire.media-picker', [
            'folders' => $this->folders,
            'media' => $this->media,
            'currentFolder' => $this->currentFolder,
        ]);
    }
}
